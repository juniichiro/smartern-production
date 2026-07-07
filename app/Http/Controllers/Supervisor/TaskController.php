<?php

namespace App\Http\Controllers\Supervisor;

use App\Events\RealTimeTaskAssigned;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Requests\Supervisor\StoreTaskRequest;
use App\Http\Requests\Supervisor\UpdateTaskRequest;
use App\Http\Requests\Supervisor\UpdateTaskStatusRequest;
use App\Mail\TaskNotificationMail;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Task;
use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class TaskController extends Controller
{
    protected $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display the main monitoring dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return view('supervisor.task-monitoring', [
                'interns' => collect([]),
                'eligibleInterns' => collect([]),
                'totalRendered' => 0,
                'totalRequirement' => 0,
                'remainingHours' => 0,
                'completionPercentage' => 0,
            ]);
        }

        $officeId = $supervisor->office_id;

        $query = InternDetail::query();
        $query->where('office_id', $officeId);

        $interns = $query->whereDoesntHave('certificate', function ($query) {
            $query->where('status', 'published');
        })
            ->withCount(['tasks' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->with(['user'])
            ->get();

        // Filter for interns who do NOT have a published certificate for assignment dropdown in this office
        $eligibleInterns = InternDetail::eligibleForTasks()
            ->where('office_id', $officeId)
            ->get();

        $totalRendered = (float) ($interns->sum('hours_rendered') ?? 0);
        $totalRequirement = (int) $interns->sum('required_hours');
        $remainingHours = max(0, $totalRequirement - $totalRendered);

        $completionPercentage = $totalRequirement > 0
            ? (int) round(($totalRendered / $totalRequirement) * 100)
            : 0;

        return view('supervisor.task-monitoring', compact(
            'interns',
            'eligibleInterns',
            'totalRendered',
            'totalRequirement',
            'remainingHours',
            'completionPercentage'
        ));
    }

    /**
     * Dispatch new tasks to interns with restriction for completed internships
     */
    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $user = Auth::user();
        $officeId = $user->supervisorDetails->office_id;

        if ($request->intern_id === 'all') {
            // Restriction: Only assign to those without a published certificate in this office
            $internIds = InternDetail::eligibleForTasks()
                ->where('office_id', $officeId)
                ->pluck('user_id');
        } else {
            // Restriction: Individual check for published certificate
            $intern = InternDetail::byPublicOrId($request->intern_id)
                ->with('certificate')
                ->firstOrFail();

            if ($intern->certificate && $intern->certificate->isPublished()) {
                return redirect()->back()->with('error', 'Assignment Restricted: This intern has already graduated and received their certificate.');
            }
            $internIds = [$intern->user_id];
        }

        foreach ($internIds as $id) {
            $task = Task::create([
                'intern_id' => $id,
                'supervisor_id' => Auth::id(),
                'office_id' => $officeId,
                'title' => $request->title,
                'description' => $request->description,
                'status' => 'pending',
                'due_date' => $request->due_date,
                'is_file_required' => $request->is_file_required,
            ]);

            $this->auditService->log('Task Assigned', "Task: '{$request->title}' assigned to User #{$id}");

            $notification = NotificationController::send(
                $id,
                'Task Assignment',
                'task',
                "You have been assigned a new task: '{$request->title}'. Please review the requirements and deadline.",
                route('intern.tasks.show', $task),
                false // isExternal
            );

            try {
                $recipient = User::find($id);
                if ($recipient && $recipient->email) {
                    Mail::to($recipient->email)->send(new TaskNotificationMail($task, $notification));
                }
            } catch (\Exception $e) {
                Log::error("Failed to send task assignment email to user {$id}: ".$e->getMessage());
            }

            // Dispatch Real-Time Event
            try {
                broadcast(new RealTimeTaskAssigned($task));
            } catch (\Exception $e) {
                // Suppress broadcast errors if Reverb is offline
            }
        }

        return redirect()->back()->with('success', 'Tasks assigned & dispatched.');
    }

    /**
     * View all tasks for a specific intern
     */
    public function internTasks(User $user)
    {
        $currentUser = Auth::user();
        $intern = InternDetail::where('user_id', $user->id)->with(['user', 'office'])->firstOrFail();

        // Authorization: Check if supervisor has access to this intern's office
        if ($intern->office_id !== $currentUser->supervisorDetails->office_id) {
            abort(403, 'Unauthorized.');
        }

        $tasks = Task::where('intern_id', $intern->user_id)->orderBy('due_date', 'asc')->get();

        return view('supervisor.tasks', compact('intern', 'tasks'));
    }

    /**
     * Show task details for review
     */
    public function show($taskKey)
    {
        try {
            $id = Hashids::decode($taskKey)[0] ?? null;
            $task = Task::find($id);

            if (! $task) {
                return redirect()->route('supervisor.tasks')
                    ->with('error', 'We couldn\'t find that task. It may have been withdrawn or removed.');
            }

            $this->authorize('view', $task);

            $task->load(['submissions', 'internDetails.user']);

            return view('supervisor.tasks-show', compact('task'));

        } catch (\Exception $e) {
            return redirect()->route('supervisor.tasks')
                ->with('error', 'The task link is invalid or has expired.');
        }
    }

    /**
     * Update task details
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'is_file_required' => $request->is_file_required,
        ]);

        $this->auditService->log('Task Updated', "Task #{$task->id}: '{$request->title}'");

        return redirect()->back()->with('success', 'Task details refined.');
    }

    /**
     * Approve or Request Revision
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $updateData = [
            'status' => $request->status,
            'remarks' => $request->remarks,
        ];

        if ($request->filled('new_due_date')) {
            $updateData['due_date'] = $request->new_due_date;
        }

        $task->update($updateData);

        $this->auditService->log('Task Status Updated', "Task #{$task->id} status set to {$request->status}".($request->filled('new_due_date') ? " with new deadline: {$request->new_due_date}" : ''));

        $targetUrl = route('intern.tasks.show', $task);

        if ($request->status === 'completed') {
            if (now()->isFriday()) {
                $hasAttendance = Attendance::where('intern_id', $task->intern_id)
                    ->whereDate('clock_in', now()->toDateString())
                    ->exists();

                if (! $hasAttendance) {
                    Attendance::create([
                        'intern_id' => $task->intern_id,
                        'office_id' => $task->office_id,
                        'clock_in' => now()->startOfDay()->hour(8),
                        'clock_out' => now()->startOfDay()->hour(17),
                        'status' => 'on_time',
                        'remarks' => "Remote Friday - Approved Task: {$task->title}",
                    ]);

                    if ($task->internDetails) {
                        $task->internDetails->increment('hours_rendered', 8);
                    }
                }
            }

            $notification = NotificationController::send(
                $task->intern_id,
                'Task Approved',
                'task',
                "Your submission for '{$task->title}' has been reviewed and approved. Well done!",
                $targetUrl,
                false // isExternal
            );

            try {
                $recipient = User::find($task->intern_id);
                if ($recipient && $recipient->email) {
                    Mail::to($recipient->email)->send(new TaskNotificationMail($task, $notification));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send task approved email: '.$e->getMessage());
            }

            $message = 'Task approved!';
        } elseif ($request->status === 'revision') {
            $notification = NotificationController::send(
                $task->intern_id,
                'Revision Requested',
                'task',
                "A revision has been requested for '{$task->title}'. Please check the feedback and re-submit.",
                $targetUrl,
                false // isExternal
            );

            try {
                $recipient = User::find($task->intern_id);
                if ($recipient && $recipient->email) {
                    $dueDateModified = $task->wasChanged('due_date');
                    Mail::to($recipient->email)->send(new TaskNotificationMail($task, $notification, [
                        'due_date_modified' => $dueDateModified,
                    ]));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send revision requested email: '.$e->getMessage());
            }

            $message = 'Revision requested.';
        } else {
            $message = 'Status updated.';
        }

        return redirect()->route('supervisor.tasks.intern', $task->internDetails->user)->with('success', $message);
    }

    /**
     * Delete a task
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $title = $task->title;
        $task->delete();

        $this->auditService->log('Task Deleted', "Task: '{$title}' removed");

        return redirect()->back()->with('success', 'Task withdrawn.');
    }
}
