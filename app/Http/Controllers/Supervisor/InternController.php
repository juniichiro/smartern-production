<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\InternDetail;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return view('supervisor.interns', [
                'interns' => collect([]),
                'totalInterns' => 0,
            ]);
        }

        $query = InternDetail::query();
        $officeId = $supervisor->office_id;
        $query->where('office_id', $officeId);

        // Eager load 'certificate' and filter out those already published
        $query->whereDoesntHave('certificate', function ($query) {
            $query->where('status', 'published');
        })
            ->with(['user', 'certificate']);

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->search.'%')
                    ->orWhere('last_name', 'like', '%'.$request->search.'%')
                    ->orWhere('user_id', 'like', '%'.$request->search.'%');
            });
        }

        $interns = $query->paginate(10)->withQueryString();

        // Update count to reflect only those currently active in this office
        $totalInterns = InternDetail::where('office_id', $officeId)
            ->whereDoesntHave('certificate', function ($q) {
                $q->where('status', 'published');
            })->count();

        return view('supervisor.interns', compact('interns', 'totalInterns'));
    }

    public function show(User $user, AttendanceService $attendanceService)
    {
        // Pull the intern with corrected relationship names
        $intern = InternDetail::where('user_id', $user->id)
            ->with(['user', 'tasks', 'attendances', 'office.supervisorDetails', 'certificate'])
            ->firstOrFail();

        // 1. Fetch complete merged logs
        $allLogs = $attendanceService->getMergedLogs($intern->user);

        // 2. Metric calculations from Merged Logs (source of truth)
        $tasks = $intern->tasks ?? collect();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();

        $absents = $allLogs->where('status', 'absent')->count();
        $lates = $allLogs->where('status', 'late')->count();
        $excused = $allLogs->where('status', 'excused')->count();

        // 3. Get latest entries for preview
        $recentTasks = $intern->tasks()->latest()->take(5)->get();
        $recentAttendance = $allLogs->take(5);

        // 4. Fetch school schedule for the timeline
        $schoolSchedule = null;
        if ($intern->school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();
        }

        // 5. Get today's effective schedule
        $effectiveSchedule = $attendanceService->getEffectiveSchedule($intern->user, now());

        return view('supervisor.intern-profile', compact(
            'intern',
            'totalTasks',
            'completedTasks',
            'absents',
            'lates',
            'excused',
            'recentTasks',
            'recentAttendance',
            'schoolSchedule',
            'effectiveSchedule'
        ));
    }

    /**
     * Update the intern limit/slots for the supervisor's office and notify head supervisors.
     */
    public function updateRequirements(Request $request)
    {
        $request->validate([
            'intern_limit' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor || ! $supervisor->office) {
            return redirect()->back()->with('error', 'Unauthorized or office not associated.');
        }

        $office = $supervisor->office;
        $office->update([
            'intern_limit' => $request->intern_limit,
        ]);

        // Find Head Supervisors (Ms. Mal-in) and notify them
        $headSupervisorRole = Role::where('name', 'head_supervisor')->first();
        if ($headSupervisorRole) {
            $headSupervisors = User::where('role_id', $headSupervisorRole->id)->get();
            foreach ($headSupervisors as $headSupervisor) {
                NotificationController::send(
                    $headSupervisor->id,
                    'Intern Requirements Update',
                    'system',
                    "Office {$office->name} has updated their intern limit to {$request->intern_limit} slots.",
                    route('head-supervisor.offices.index')
                );
            }
        }

        return redirect()->back()->with('success', 'Intern requirements updated successfully.');
    }
}
