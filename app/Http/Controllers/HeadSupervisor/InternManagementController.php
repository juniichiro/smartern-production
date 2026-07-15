<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class InternManagementController extends Controller
{
    public function index()
    {
        // Statistics
        // Active: Not yet published certificate
        $activeCount = InternDetail::whereDoesntHave('certificate', function ($q) {
            $q->where('status', 'published');
        })->count();

        // Completed: Published certificate
        $completedCount = InternDetail::whereHas('certificate', function ($q) {
            $q->where('status', 'published');
        })->count();

        // Average progress calculation
        $avgProgress = InternDetail::selectRaw('AVG(hours_rendered / COALESCE(NULLIF(required_hours, 0), 480) * 100) as avg_prog')->value('avg_prog') ?? 0;
        $avgProgress = min(100, $avgProgress);

        // Main Intern Table (With Relationships - Paginated)
        $interns = InternDetail::with(['user', 'office', 'certificate'])
            ->orderBy('last_name', 'asc')
            ->paginate(10);

        // Department Distribution
        $distribution = Office::withCount('internDetails')->get();

        $offices = Office::orderBy('name', 'asc')->get();

        return view('head-supervisor.interns', compact(
            'activeCount',
            'completedCount',
            'avgProgress',
            'interns',
            'distribution',
            'offices'
        ));
    }

    public function myInterns(Request $request)
    {
        $supervisor = auth()->user()->supervisorDetails;

        if (!$supervisor || !$supervisor->office) {
            return redirect()->route('head-supervisor.interns')
                ->with('error', 'You are not assigned to a specific office.');
        }

        $officeId = $supervisor->office_id;

        $query = InternDetail::where('office_id', $officeId)
            ->whereDoesntHave('certificate', fn($q) => $q->where('status', 'published'))
            ->with(['user', 'certificate']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->search.'%')
                    ->orWhere('last_name', 'like', '%'.$request->search.'%')
                    ->orWhere('user_id', 'like', '%'.$request->search.'%');
            });
        }

        $interns = $query->paginate(10)->withQueryString();

        $totalInterns = InternDetail::where('office_id', $officeId)
            ->whereDoesntHave('certificate', fn($q) => $q->where('status', 'published'))
            ->count();

        return view('head-supervisor.my-interns', compact('interns', 'totalInterns'));
    }

    /**
     * Display the specified intern's profile and progress
     */
    public function show(User $user, AttendanceService $attendanceService)
    {
        // Eager load ALL relationships needed for the tabs, ordering journals to show most recent first
        $intern = InternDetail::where('user_id', $user->id)->with([
            'user', 
            'office', 
            'tasks', 
            'attendances', 
            'extensions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'journals' => function ($query) {
                $query->orderBy('entry_date', 'desc')->orderBy('created_at', 'desc');
            }
        ])->firstOrFail();

        $targetHours = $intern->required_hours ?? 480;
        $currentHours = $intern->hours_rendered ?? 0;
        $hoursProgress = $targetHours > 0 ? ($currentHours / $targetHours) * 100 : 0;

        // Statistics for the progress overview cards
        $totalTasks = $intern->tasks->count();
        $completedTasks = $intern->tasks->where('status', 'completed')->count();
        $taskCompletionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        // Get Effective Schedule for Today
        $effectiveSchedule = $attendanceService->getEffectiveSchedule($intern->user, now());

        // Fetch official school schedule for timeline
        $schoolSchedule = null;
        $holidaysAndSuspensions = [];
        if ($intern->school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();

            $holidaysAndSuspensions = Schedule::where('school_name', $intern->school)
                ->whereIn('type', ['holiday', 'suspension'])
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        }

        // Fetch Merged Logs for consistent history
        $attendanceHistory = $attendanceService->getMergedLogs($intern->user);

        return view('head-supervisor.intern-profile', compact(
            'intern',
            'targetHours',
            'hoursProgress',
            'totalTasks',
            'completedTasks',
            'taskCompletionRate',
            'effectiveSchedule',
            'schoolSchedule',
            'attendanceHistory',
            'holidaysAndSuspensions'
        ));
    }

    /**
     * Update the reporting schedule for the specified intern
     */
    public function updateSchedule(\Illuminate\Http\Request $request, User $user)
    {
        $request->validate([
            'schedule_assignment' => 'nullable|array',
            'schedule_assignment.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'required_hours' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
        ]);

        $intern = InternDetail::where('user_id', $user->id)->firstOrFail();
        
        $startDate = $request->input('start_date') ?? $intern->start_date;
        $requiredHours = $request->input('required_hours');
        $scheduleDays = $request->input('schedule_assignment') ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        $dailyHours = 8;
        $holidays = [];
        if ($intern->school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();
            if ($schoolSchedule) {
                $dailyHours = $schoolSchedule->daily_hours ?? 8;
            }
            $holidays = Schedule::where('school_name', $intern->school)
                ->whereIn('type', ['holiday', 'suspension'])
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        }

        $newEndDate = $this->calculateExpectedEndDate($startDate, $requiredHours, $dailyHours, $scheduleDays, $holidays);

        $intern->update([
            'schedule_assignment' => $scheduleDays,
            'required_hours' => $requiredHours,
            'start_date' => $startDate,
            'end_date' => $newEndDate,
        ]);

        return redirect()->back()->with('success', 'Intern reporting schedule updated successfully.');
    }

    private function calculateExpectedEndDate($startDate, $requiredHours, $dailyHours, $scheduleDays, $holidays)
    {
        if (!$startDate || !$requiredHours || $requiredHours <= 0 || empty($scheduleDays)) {
            return null;
        }

        $hoursLeft = $requiredHours;
        $currentDate = \Carbon\Carbon::parse($startDate);
        $safetyCounter = 0;
        $maxIterations = 365 * 5;

        while ($hoursLeft > 0 && $safetyCounter < $maxIterations) {
            $safetyCounter++;
            $currentDayName = $currentDate->format('l');
            $dateStr = $currentDate->format('Y-m-d');

            $isReportingDay = in_array($currentDayName, $scheduleDays);
            $isNonWorkingDay = in_array($dateStr, $holidays);

            if ($isReportingDay && !$isNonWorkingDay) {
                $hoursLeft -= $dailyHours;
            }

            if ($hoursLeft > 0) {
                $currentDate->addDay();
            }
        }

        return $currentDate->format('Y-m-d');
    }

    /**
     * Resolve the date extension request for an intern.
     */
    public function resolveExtension(\Illuminate\Http\Request $request, \App\Models\InternExtension $extension)
    {
        $request->validate([
            'status' => 'required|string|in:approved,rejected',
            'rejection_note' => 'nullable|string|required_if:status,rejected|max:255',
        ]);

        $status = $request->input('status');
        $rejectionNote = $request->input('rejection_note');

        $extension->update([
            'status' => $status,
            'resolved_by' => auth()->id(),
            'rejection_note' => $status === 'rejected' ? $rejectionNote : null,
        ]);

        if ($status === 'approved') {
            $intern = $extension->internDetail;
            $intern->update([
                'end_date' => $extension->new_end_date,
            ]);
        }

        try {
            $intern = $extension->internDetail;
            if ($intern) {
                $type = $status === 'approved' ? 'Extension Approved' : 'Extension Rejected';
                $message = $status === 'approved'
                    ? 'Your date extension request has been approved. Your new end date is ' . $extension->new_end_date->format('M d, Y') . '.'
                    : 'Your date extension request has been rejected.' . ($rejectionNote ? " Reason: {$rejectionNote}" : '');
                NotificationController::send(
                    $intern->user_id,
                    $type,
                    'system',
                    $message,
                    route('intern.schedule.index'),
                    true
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to notify intern of extension resolution: ' . $e->getMessage());
        }

        $message = $status === 'approved' ? 'Extension request approved.' : 'Extension request rejected.';
        return redirect()->back()->with('success', $message);
    }
}
