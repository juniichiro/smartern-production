<?php

namespace App\Http\Controllers\Intern;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $intern = $user->internDetails;

        // If intern details are missing or no office is assigned, we should handle this gracefully
        // For now, we'll provide defaults to avoid crashing
        if (! $intern || ! $intern->office_id) {
            return view('intern.dashboard', [
                'rendered' => 0,
                'totalRequired' => $intern ? ($intern->required_hours ?? 480) : 480,
                'percentage' => 0,
                'pendingTasksCount' => 0,
                'logs' => collect([]),
                'deadlines' => collect([]),
                'activeTask' => null,
                'announcements' => collect([]),
                'notifications' => collect([]),
                'attendanceStatus' => 'TIME IN',
                'attendanceSub' => ! $intern ? 'Complete your profile first' : 'Waiting for office assignment',
                'attendanceColor' => 'text-gray-400',
            ]);
        }

        // Calculate Hours
        $totalRequired = $intern->required_hours ?? $intern->office->total_hours_required ?? 480;
        $rendered = $intern->hours_rendered ?? 0;
        $percentage = $totalRequired > 0 ? round(($rendered / $totalRequired) * 100) : 0;

        // Count Pending Tasks (Tasks not yet completed)
        $pendingTasksCount = Task::where('intern_id', $user->id)
            ->whereIn('status', ['pending', 'revision'])
            ->count();

        // Fetch Recent Logs (Attendance)
        $logs = Attendance::where('intern_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Fetch Tasks for the Tabbed Queue
        $deadlines = Task::where('intern_id', $user->id)
            ->with(['supervisor.supervisorDetails', 'submissions'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Fetch Most Urgent Active Task
        $activeTask = Task::where('intern_id', $user->id)
            ->whereIn('status', ['pending', 'revision'])
            ->orderBy('due_date', 'asc')
            ->first();

        // Fetch Recent Announcements (last
        $officeId = $intern->office_id;
        $announcements = Announcement::where(function ($query) use ($officeId) {
            $query->where('office_id', $officeId)
                  ->orWhereNull('office_id');
        })
            ->with('user.supervisorDetails')
            ->latest()
            ->take(3)
            ->get();

        // Attendance Status
        $todayRecord = Attendance::where('intern_id', $user->id)
            ->whereDate('clock_in', now()->toDateString())
            ->whereNull('clock_out')
            ->first();

        $completedToday = Attendance::where('intern_id', $user->id)
            ->whereDate('clock_in', now()->toDateString())
            ->whereNotNull('clock_out')
            ->exists();

        $attendanceStatus = 'TIME IN';
        $attendanceSub = 'Scan QR Code';
        $attendanceColor = 'text-gray-800 dark:text-gray-100';

        if ($todayRecord) {
            $attendanceStatus = 'TIME OUT';
            $attendanceSub = 'Ready to Clock Out';
            $attendanceColor = 'text-orange-500 dark:text-orange-400';
        } elseif ($completedToday) {
            $attendanceStatus = 'LOGGED';
            $attendanceSub = '8 Hours Earned';
            $attendanceColor = 'text-emerald-500 dark:text-emerald-400';
        }

        // Fetch official school schedule and holidays for timeline
        $schoolSchedule = null;
        $holidaysAndSuspensions = [];
        if ($intern->school) {
            $schoolSchedule = \App\Models\Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();

            $holidaysAndSuspensions = \App\Models\Schedule::where('school_name', $intern->school)
                ->whereIn('type', ['holiday', 'suspension'])
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        }

        // Fetch any existing pending extension request
        $pendingExtension = \App\Models\InternExtension::where('intern_detail_id', $intern->id)
            ->where('status', 'pending')
            ->first();

        // Notifications (assuming you have a system or empty collection)
        $notifications = collect([]);

        return view('intern.dashboard', compact(
            'intern',
            'rendered',
            'totalRequired',
            'percentage',
            'pendingTasksCount',
            'logs',
            'deadlines',
            'activeTask',
            'announcements',
            'notifications',
            'attendanceStatus',
            'attendanceSub',
            'attendanceColor',
            'schoolSchedule',
            'holidaysAndSuspensions',
            'pendingExtension'
        ));
    }
}
