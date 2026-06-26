<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController; // Import the Notification Controller
use App\Http\Requests\Intern\StoreJournalRequest;
use App\Http\Requests\Intern\SubmitTaskRequest;
use App\Mail\TaskNotificationMail;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\SecurityAuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

class ProgressController extends Controller
{
    protected $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display the Intern's Dashboard / Progress Overview.
     */
    public function index()
    {
        $user = Auth::user();
        $intern = $user->internDetails;

        $totalRequired = $intern->required_hours ?? 480;
        $rendered = round($intern->hours_rendered ?? 0);
        $remaining = max(0, $totalRequired - $rendered);
        $percentage = $totalRequired > 0 ? round(($rendered / $totalRequired) * 100) : 0;

        $tasks = Task::where('intern_id', $user->id)
            ->with('supervisor.supervisorDetails')
            ->orderBy('due_date', 'asc')
            ->get();

        $journals = Journal::where('intern_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $hasSubmittedToday = Journal::where('intern_id', $user->id)
            ->where('entry_date', now()->format('Y-m-d'))
            ->exists();

        $schoolSchedule = null;
        if ($intern && $intern->school) {
            $schoolSchedule = \App\Models\Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();
        }

        $timelineNotSet = !$schoolSchedule || !$schoolSchedule->start_date || !$schoolSchedule->target_end_date;

        return view('intern.progress', compact(
            'rendered', 'remaining', 'percentage', 'totalRequired',
            'tasks', 'hasSubmittedToday', 'journals', 'timelineNotSet'
        ));
    }

    public function storeJournal(StoreJournalRequest $request)
    {
        $user = Auth::user();

        $journal = new Journal;

        $journal->intern_id = $user->internDetails->user_id ?? null;
        $journal->journal_type = $request->journal_type;
        $journal->title = $request->title;
        $journal->notes = $request->notes;
        $journal->entry_date = $request->entry_date;
        $journal->created_at = now();

        $includedTasks = $request->input('included_tasks');
        if (is_string($includedTasks)) {
            $includedTasks = json_decode($includedTasks, true);
        }

        $journal->content = Journal::compileContent((int) $user->id, $request->journal_type, $request->entry_date, $request->notes, $includedTasks);
        $journal->task_ids = $includedTasks;

        if ($journal->save()) {
            $this->auditService->log('Journal Entry', "Title: {$request->title}");

            return redirect()->to(route('intern.progress').'#journals')
                ->with('success', 'Journal entry finalized.');
        }

        return redirect()->back()->with('error', 'We couldn\'t save your entry. Please try again.');
    }

    /**
     * Update an existing journal entry's title and notes.
     */
    public function updateJournal(Request $request, Journal $journal)
    {
        if ((int) $journal->intern_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'included_tasks' => 'nullable',
        ]);

        $journal->title = $request->title;
        $journal->notes = $request->notes;

        $includedTasks = $request->input('included_tasks');
        if (is_string($includedTasks)) {
            $includedTasks = json_decode($includedTasks, true);
        }

        $journal->content = Journal::compileContent((int) Auth::id(), $journal->journal_type, $journal->entry_date, $request->notes, $includedTasks);
        $journal->task_ids = $includedTasks;

        if ($journal->save()) {
            $this->auditService->log('Journal Updated', "Title: {$request->title}");

            return redirect()->to(route('intern.progress').'#journals')
                ->with('success', 'Journal entry updated.');
        }

        return redirect()->back()->with('error', 'We couldn\'t update your entry. Please try again.');
    }

    /**
     * Preview compiled journal data via AJAX.
     */
    public function previewJournal(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:daily,weekly',
            'date' => 'required|date',
            'journal_id' => 'nullable',
        ]);

        $user = Auth::user();
        $internId = (int) $user->id;
        $type = $request->type;
        $date = Carbon::parse($request->date);
        $journalIdValue = $request->journal_id;

        $journal = null;
        if ($journalIdValue) {
            // Support both raw ID and Hashid for the preview lookup
            $id = is_numeric($journalIdValue) ? $journalIdValue : (Hashids::decode($journalIdValue)[0] ?? null);
            $journal = Journal::find($id);
        }

        if ($type === 'daily') {
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $suggestedTitle = 'Daily Journal - '.$date->format('F d, Y');
        } else {
            $start = $date->copy()->startOfWeek();
            $end = $date->copy()->endOfWeek();
            $suggestedTitle = 'Weekly Journal - Week of '.$start->format('F d, Y');
        }

        // Always fetch all potential tasks for the period
        $tasks = Task::where('intern_id', $internId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('due_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('status', 'completed')
                            ->whereBetween('updated_at', [$start, $end]);
                    })
                    ->orWhereHas('submissions', function ($q) use ($start, $end) {
                        $q->whereBetween('created_at', [$start, $end]);
                    })
                    ->orWhereIn('status', ['pending', 'revision']);
            })
            ->get();

        // Determine which IDs should be selected by default
        if ($journal && $journal->task_ids !== null) {
            $selectedIds = $journal->task_ids;
        } else {
            $selectedIds = $tasks->pluck('id')->toArray();
        }

        $attendanceService = app(AttendanceService::class);
        $logs = $attendanceService->getMergedLogs($user)
            ->filter(function ($log) use ($start, $end) {
                return $log['date']->between($start->copy()->startOfDay(), $end->copy()->endOfDay());
            })
            ->sortBy(function ($log) {
                return $log['date']->timestamp;
            });

        $tasksData = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->label(),
            ];
        });

        $attendanceData = $logs->map(function ($log) {
            $timeIn = $log['clock_in'] ? $log['clock_in']->format('h:i A') : 'N/A';
            $timeOut = $log['clock_out'] ? $log['clock_out']->format('h:i A') : 'N/A';

            return [
                'date' => $log['date']->format('M d, Y'),
                'clock_in' => $timeIn,
                'clock_out' => $timeOut,
                'status' => ucfirst(str_replace('_', ' ', $log['status'])),
                'remarks' => $log['remarks'] ?: '',
            ];
        })->values();

        return response()->json([
            'suggested_title' => $suggestedTitle,
            'tasks' => $tasksData,
            'selected_ids' => $selectedIds,
            'attendance' => $attendanceData,
        ]);
    }

    /**
     * Show specific task details.
     */
    public function showTask($taskKey)
    {
        try {
            $id = Hashids::decode($taskKey)[0] ?? null;
            $task = Task::find($id);

            if (! $task) {
                return redirect()->route('intern.progress')
                    ->with('error', 'We couldn\'t find that task. It may have been withdrawn by your supervisor.');
            }

            // Security check: Only allow the assigned intern to view the task
            if ($task->intern_id !== auth()->id()) {
                abort(403, 'Unauthorized action.');
            }

            return view('intern.tasks-show', compact('task'));

        } catch (\Exception $e) {
            return redirect()->route('intern.progress')
                ->with('error', 'The task link is invalid or has expired.');
        }
    }

    /**
     * Handle Task Submission + Notify Supervisor with a direct link to the Review page.
     */
    public function submitTask(SubmitTaskRequest $request, Task $task)
    {
        try {
            $filePath = null;
            $originalFilename = null;

            if ($request->hasFile('submission_file')) {
                $file = $request->file('submission_file');
                $originalFilename = $file->getClientOriginalName();

                // Cleanup: Delete previous submission file for this task if it exists
                if ($task->submissions()->exists()) {
                    $oldSubmission = $task->submissions()->latest()->first();
                    if ($oldSubmission && $oldSubmission->file_path) {
                        $relativeStoragePath = str_replace('storage/', '', $oldSubmission->file_path);
                        Storage::disk('public')->delete($relativeStoragePath);
                    }
                }

                $path = $file->store('tasks', 'public');
                $filePath = 'storage/' . $path;
            }

            // Create submission record
            Submission::create([
                'task_id' => $task->id,
                'intern_id' => Auth::id(),
                'content' => $request->content,
                'file_path' => $filePath,
                'original_filename' => $originalFilename,
            ]);

            // Update task status and clear previous remarks so Supervisor gets a clean review screen
            $task->update([
                'status' => 'pending',
                'remarks' => null,
            ]);

            $this->auditService->log('Task Submitted', "Task: '{$task->title}' submitted for review");

            // NOTIFICATION: Direct link for the supervisor to review the submission
            $notification = NotificationController::send(
                $task->supervisor_id,
                'Intern Submission',
                'task',
                'Intern '.Auth::user()->internDetails->first_name." has submitted work for '{$task->title}' and it is ready for review.",
                route('supervisor.tasks.show', $task),
                false // isExternal
            );

            try {
                $supervisor = User::find($task->supervisor_id);
                if ($supervisor && $supervisor->email) {
                    Mail::to($supervisor->email)->send(new TaskNotificationMail($task, $notification));
                }
            } catch (\Exception $e) {
                Log::error("Failed to send task submission email to supervisor {$task->supervisor_id}: ".$e->getMessage());
            }

            return redirect()->back()->with('success', 'Task finalized & submitted.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Submission failed: '.$e->getMessage()]);
        }
    }

    /**
     * Export Accomplishment Report (PDF).
     */
    public function exportAccomplishments()
    {
        $user = Auth::user();
        $intern = $user->internDetails;

        // Fetch only completed tasks
        $tasks = Task::where('intern_id', $user->id)
            ->where('status', 'completed')
            ->with(['submissions', 'supervisor.supervisorDetails'])
            ->get();

        $data = [
            'intern' => $intern,
            'tasks' => $tasks,
            'date' => now()->format('F d, Y'),
            'total_hours' => $intern->hours_rendered,
        ];

        $pdf = Pdf::loadView('exports.accomplishments-pdf', $data);

        // Uses student_id for the filename (e.g., 2023-0001_Accomplishments.pdf)
        $fileName = ($intern->student_id ?? $intern->id).'_Accomplishments.pdf';

        $this->auditService->log('Accomplishment Export', "Exported PDF: {$fileName}");

        return $pdf->download($fileName);
    }
}
