<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Task;
use App\Models\MatchResult;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return view('supervisor.dashboard', [
                'supervisor' => null,
                'activeInternsCount' => 0,
                'pendingTasksCount' => 0,
                'completedTasksCount' => 0,
                'attendanceIssues' => 0,
                'interns' => collect([]),
                'upcomingDeadlines' => collect([]),
                'activeAttendance' => collect([]),
                'submissionsNeedingReview' => collect([]),
            ]);
        }

        $query = InternDetail::query();

        // Strictly filter by department/office for all users in the Supervisor namespace
        $officeId = $supervisor->office_id;
        $query->where('office_id', $officeId);

        // Fetch active interns (those without a 'published' certificate)
        $interns = $query->whereDoesntHave('certificate', function ($query) {
            $query->where('status', 'published');
        })
            ->with(['user', 'tasks', 'attendances'])
            ->get();

        $activeInternsCount = $interns->count();
        $internIds = $interns->pluck('user_id');

        // Overview Stats based on the filtered Active Intern list
        $pendingTasksCount = Task::whereIn('intern_id', $internIds)
            ->where('status', 'pending')
            ->count();

        $completedTasksCount = Task::whereIn('intern_id', $internIds)
            ->where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->count();

        $attendanceIssues = Attendance::whereIn('intern_id', $interns->pluck('user_id'))
            ->whereIn('status', ['late', 'absent'])
                            // FIX: Ensure you are using the correct column name (e.g., created_at)
            ->whereDate('created_at', now()->today())
            ->count();

        // Upcoming Deadlines for active interns only
        $upcomingDeadlines = Task::whereIn('intern_id', $internIds)
            ->with('internDetails')
            ->where('status', 'pending')
            ->where('due_date', '>=', now()->startOfDay())
            ->orderBy('due_date', 'asc')
            ->take(3)
            ->get();

        // Fetch Today's Active Attendance (Clocked in but not out)
        $activeAttendance = Attendance::whereIn('intern_id', $internIds)
            ->whereDate('clock_in', now()->today())
            ->whereNull('clock_out')
            ->with('internDetails')
            ->get();

        // Fetch Submissions Needing Review
        // A task needs review if it is 'pending' and HAS a submission
        $submissionsNeedingReview = Task::whereIn('intern_id', $internIds)
            ->where('status', 'pending')
            ->has('submissions')
            ->with(['internDetails', 'submissions' => function ($q) {
                $q->latest();
            }])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('supervisor.dashboard', compact(
            'supervisor',
            'activeInternsCount',
            'pendingTasksCount',
            'completedTasksCount',
            'attendanceIssues',
            'interns',
            'upcomingDeadlines',
            'activeAttendance',
            'submissionsNeedingReview'
        ));
    }

    public function officeMatching()
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return redirect()->route('supervisor.dashboard')->with('error', 'Supervisor details not found.');
        }

        $officeId = $supervisor->office_id;
        $office = $supervisor->office;

        // Get the list of applicant names that are already finalized/assigned to an office
        $finalizedApplicants = MatchResult::where('is_confirmed', true)
            ->pluck('applicant_name')
            ->toArray();

        // Fetch top 5 match results for this specific office belonging to unassigned applicants
        $topMatches = MatchResult::where('office_id', $officeId)
            ->where('is_confirmed', false)
            ->whereNotIn('applicant_name', $finalizedApplicants)
            ->orderByDesc('score')
            ->take(5)
            ->get();

        return view('supervisor.office-matching', compact('supervisor', 'office', 'topMatches'));
    }

    public function requestIntern(MatchResult $matchResult)
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor || $matchResult->office_id !== $supervisor->office_id) {
            return back()->with('error', 'Unauthorized action.');
        }

        $matchResult->update(['requested_by_supervisor' => true]);

        // Dispatch database and real-time notification to all Head Supervisors
        $headSupervisors = \App\Models\User::where('role_id', \App\Enums\UserRole::HEAD_SUPERVISOR->value)->get();
        foreach ($headSupervisors as $hs) {
            \App\Http\Controllers\NotificationController::send(
                $hs->id,
                'info',
                'Placement Request',
                "The {$supervisor->office->name} Supervisor has requested {$matchResult->applicant_name} for placement.",
                route('head-supervisor.matching')
            );
        }

        return back()->with('success', "Intern nomination request submitted to the Head Supervisor for {$matchResult->applicant_name}.");
    }
}
