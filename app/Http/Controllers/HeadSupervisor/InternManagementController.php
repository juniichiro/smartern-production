<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AttendanceService;

class InternManagementController extends Controller
{
    public function index()
    {
        // 1. Statistics
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

        // 2. Main Intern Table (With Relationships)
        $interns = InternDetail::with(['user', 'office', 'certificate'])
            ->orderBy('last_name', 'asc')
            ->get();

        // 3. Department Distribution
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

    /**
     * Display the specified intern's profile and progress.
     */
    public function show(User $user, AttendanceService $attendanceService)
    {
        // Eager load ALL relationships needed for the tabs, ordering journals to show most recent first
        $intern = InternDetail::where('user_id', $user->id)->with([
            'user', 
            'office', 
            'tasks', 
            'attendances', 
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
        if ($intern->school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();
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
            'attendanceHistory'
        ));
    }

    /**
     * Update the reporting schedule for the specified intern.
     */
    public function updateSchedule(\Illuminate\Http\Request $request, User $user)
    {
        $request->validate([
            'schedule_assignment' => 'nullable|array',
            'schedule_assignment.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'required_hours' => 'required|integer|min:1',
        ]);

        $intern = InternDetail::where('user_id', $user->id)->firstOrFail();
        $intern->update([
            'schedule_assignment' => $request->input('schedule_assignment'),
            'required_hours' => $request->input('required_hours'),
        ]);

        return redirect()->back()->with('success', 'Intern reporting schedule updated successfully.');
    }
}
