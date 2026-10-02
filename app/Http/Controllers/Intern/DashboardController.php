<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\InternExtension;
use App\Models\Schedule;
use App\Models\Task;
use App\Services\InternAttendanceStateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected InternAttendanceStateService $attendanceStateService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $intern = $user->internDetails;
        $now = now()->timezone('Asia/Manila');
        $todayDateFormatted = $now->format('l, F j');

        // Handle case where profile is incomplete or unassigned gracefully
        if (! $intern || ! $intern->office_id) {
            return view('intern.dashboard', [
                'user' => $user,
                'intern' => $intern,
                'todayDate' => $todayDateFormatted,
                'rendered' => 0,
                'totalRequired' => $intern ? ($intern->required_hours ?? 480) : 480,
                'percentage' => 0,
                'remaining' => $intern ? ($intern->required_hours ?? 480) : 480,
                'thisWeekHours' => 0,
                'paceEstimate' => null,
                'attendanceState' => [
                    'state' => 'unavailable',
                    'headline' => 'Attendance unavailable',
                    'subtext' => ! $intern ? 'Please complete your intern profile first.' : 'Waiting for department assignment.',
                    'duration' => null,
                    'clock_in_formatted' => null,
                    'clock_out_formatted' => null,
                    'clock_in_epoch_ms' => 0,
                    'server_now_epoch_ms' => $now->getTimestamp() * 1000,
                    'elapsed_seconds' => 0,
                    'button_label' => null,
                    'button_action' => null,
                    'button_url' => null,
                    'can_scan' => false,
                    'status_pill' => ['label' => 'Profile incomplete', 'color' => 'slate'],
                ],
                'logs' => collect([]),
                'todoTasks' => collect([]),
                'submittedTasks' => collect([]),
                'verifiedTasks' => collect([]),
                'announcements' => collect([]),
                'notifications' => collect([]),
                'schoolSchedule' => null,
                'holidaysAndSuspensions' => [],
                'pendingExtension' => null,
                'hasApprovedExtension' => false,
            ]);
        }

        // 1. Calculate Hours & Progress Metrics
        $totalRequired = (float) ($intern->required_hours ?? $intern->office->total_hours_required ?? 480);
        $rendered = (float) ($intern->hours_rendered ?? 0);
        $percentage = $totalRequired > 0 ? ($rendered / $totalRequired) * 100 : 0;
        $remaining = (float) max(0, $totalRequired - $rendered);

        // 2. Weekly Hours & Pace Estimate
        $thisWeekHours = $this->attendanceStateService->calculateWeeklyHours($user);
        $paceEstimate = $this->attendanceStateService->calculatePaceEstimate($user, $rendered, $totalRequired);

        // 3. State-Aware Attendance Card
        $attendanceState = $this->attendanceStateService->resolveState($user);

        // 4. Recent Attendance Logs (Last 5, newest first)
        $logs = Attendance::where('intern_id', $user->id)
            ->orderBy('clock_in', 'desc')
            ->take(5)
            ->get();

        // 5. Tasks for the Tabbed Queue (With overdue sorting first in To-Do)
        $allTasks = Task::where('intern_id', $user->id)
            ->with(['supervisor.supervisorDetails', 'submissions'])
            ->orderBy('due_date', 'asc')
            ->get();

        $todoTasks = $allTasks->filter(function ($t) {
            return $t->status->value === 'revision' || ($t->status->value === 'pending' && $t->submissions->count() === 0);
        })->sortBy(function ($t) use ($now) {
            $dueDate = $t->due_date ? Carbon::parse($t->due_date)->timezone('Asia/Manila') : null;
            $isOverdue = ($dueDate && $dueDate->isPast()) ? 0 : 1;
            return sprintf('%d_%s', $isOverdue, $dueDate ? $dueDate->toIso8601String() : '9999-12-31');
        })->values();

        $submittedTasks = $allTasks->filter(function ($t) {
            return $t->status->value === 'pending' && $t->submissions->count() > 0;
        })->values();

        $verifiedTasks = $allTasks->filter(function ($t) {
            return $t->status->value === 'completed';
        })->values();

        // 6. Recent Office Announcements (Scoped to intern's office, latest 3)
        $officeId = $intern->office_id;
        $announcements = Announcement::where(function ($query) use ($officeId) {
            $query->where('office_id', $officeId)
                  ->orWhereNull('office_id');
        })
            ->with('user.supervisorDetails')
            ->latest()
            ->take(3)
            ->get();

        // 7. Official School Schedule & Holidays for Timeline / Projections
        $schoolSchedule = null;
        $holidaysAndSuspensions = [];
        if ($intern->school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();

            $holidaysAndSuspensions = Schedule::where('school_name', $intern->school)
                ->whereIn('type', ['holiday', 'suspension'])
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        }

        // 8. Extension Requests
        $pendingExtension = InternExtension::where('intern_detail_id', $intern->id)
            ->where('status', 'pending')
            ->first();

        $hasApprovedExtension = InternExtension::where('intern_detail_id', $intern->id)
            ->where('status', 'approved')
            ->exists();

        $notifications = collect([]);

        return view('intern.dashboard', compact(
            'user',
            'intern',
            'todayDateFormatted',
            'rendered',
            'totalRequired',
            'percentage',
            'remaining',
            'thisWeekHours',
            'paceEstimate',
            'attendanceState',
            'logs',
            'todoTasks',
            'submittedTasks',
            'verifiedTasks',
            'announcements',
            'notifications',
            'schoolSchedule',
            'holidaysAndSuspensions',
            'pendingExtension',
            'hasApprovedExtension'
        ));
    }
}
