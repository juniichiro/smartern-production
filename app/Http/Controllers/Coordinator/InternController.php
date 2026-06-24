<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\Schedule;
use App\Models\SupervisorDetail;
use App\Models\User;
use App\Services\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class InternController extends Controller
{
    public function index(Request $request)
    {
        $coordinator = Auth::user()->schoolCoordinatorDetails;
        $school = $coordinator->school;

        $query = $this->applyFilters($request, $school);

        $interns = $query->paginate(10);

        $offices = Cache::remember('all_offices', 3600, function () {
            return Office::all();
        });

        if ($request->ajax()) {
            return view('coordinator.partials._intern_table', compact('interns'))->render();
        }

        return view('coordinator.interns', compact('interns', 'offices'));
    }

    public function exportPDF(Request $request)
    {
        $coordinator = Auth::user()->schoolCoordinatorDetails;
        $school = $coordinator->school;

        $query = $this->applyFilters($request, $school);
        $interns = $query->get();

        // Logo Base64 encoding
        $logoSrc = null;
        $logoPath = public_path('images/blue-smartern.png');
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,'.$logoData;
        }

        $pdf = Pdf::loadView('exports.intern-registry-pdf', [
            'logo' => $logoSrc,
            'school' => $school,
            'date' => now()->format('F d, Y'),
            'interns' => $interns,
            'departmentCount' => $interns->pluck('office_id')->unique()->count(),
        ]);

        return $pdf->setPaper('a4', 'portrait')->download('Intern_Registry_'.now()->format('Y-m-d').'.pdf');
    }

    private function applyFilters(Request $request, $school)
    {
        $query = InternDetail::with(['user', 'office', 'certificate'])
            ->withCount(['attendances as absent_count' => function ($q) {
                $q->where('status', 'absent');
            }])
            ->withCount(['attendances as late_count' => function ($q) {
                $q->where('status', 'late');
            }])
            ->withCount(['attendances as excused_count' => function ($q) {
                $q->where('status', 'excused');
            }])
            ->where('school', $school);

        // Advanced Filtering
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('display_id', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if ($request->filled('office')) {
            $query->where('office_id', $request->office);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'completed') {
                $query->whereHas('certificate', fn ($q) => $q->where('status', 'published'));
            } elseif ($status === 'endorsing') {
                $query->whereColumn('hours_rendered', '>=', 'required_hours')
                    ->whereDoesntHave('certificate', fn ($q) => $q->where('status', 'published'));
            } elseif ($status === 'active') {
                $query->whereColumn('hours_rendered', '<', 'required_hours')
                    ->whereDoesntHave('certificate', fn ($q) => $q->where('status', 'published'));
            }
        }

        if ($request->boolean('risk')) {
            $query->whereHas('attendances', fn ($q) => $q->where('status', 'absent'), '>', 2);
        }

        return $query;
    }

    public function show(User $user, AttendanceService $attendanceService)
    {
        $id = $user->id; // Using stable internal ID for cache key
        $cacheKey = "coordinator_intern_show_{$id}";

        $data = Cache::remember($cacheKey, 120, function () use ($user, $attendanceService) {
            // 1. Fetch the specific intern record with all required relations
            $intern = InternDetail::where('user_id', $user->id)
                ->with(['user', 'office', 'certificate', 'tasks', 'journals'])
                ->firstOrFail();

            // 2. Separately fetch the supervisor
            $supervisor = SupervisorDetail::where('office_id', $intern->office_id)
                ->with('user')
                ->first();

            // 3. Metric calculations (Calculated from the comprehensive Merged Logs)
            $allLogs = $attendanceService->getMergedLogs($intern->user);

            $totalTasks = $intern->tasks->count();
            $completedTasks = $intern->tasks->where('status', TaskStatus::COMPLETED)->count();

            $absents = $allLogs->where('status', 'absent')->count();
            $lates = $allLogs->where('status', 'late')->count();
            $excused = $allLogs->where('status', 'excused')->count();

            // 4. Training constants
            $requiredHours = $intern->required_hours ?? 480;
            $renderedHours = $intern->hours_rendered ?? 0;

            // 5. Training Log (Timeline)
            $timeline = collect();

            $intern->journals->each(fn ($j) => $timeline->push([
                'type' => 'journal',
                'title' => 'Submitted a Journal',
                'description' => $j->title,
                'date' => $j->created_at,
                'icon' => 'book',
            ]));

            // Add all merged logs to timeline (Actual, Excused, Holidays, etc.)
            $allLogs->each(function ($log) use ($timeline) {
                // Only add significant events to timeline
                if (in_array($log['status'], ['on_time', 'late', 'absent', 'excused', 'holiday', 'suspension'])) {
                    $description = match ($log['status']) {
                        'on_time' => 'Signed in on time',
                        'late' => 'Signed in late',
                        'absent' => 'Marked as absent',
                        'excused' => 'Excused from shift',
                        'holiday' => 'Holiday: '.$log['remarks'],
                        'suspension' => 'Suspension: '.$log['remarks'],
                        default => ucfirst($log['status'])
                    };

                    $timeline->push([
                        'type' => 'attendance',
                        'title' => $log['type'] === 'actual' ? 'Logged Attendance' : 'Schedule Update',
                        'description' => $description,
                        'date' => $log['clock_in'] ?? $log['date']->copy()->setTime(8, 0),
                        'icon' => $log['status'] === 'absent' ? 'alert-circle' : 'clock',
                    ]);
                }
            });

            $intern->tasks->each(fn ($t) => $timeline->push([
                'type' => 'task',
                'title' => 'Updated a Task',
                'description' => $t->title.' ('.$t->status->label().')',
                'date' => $t->updated_at,
                'icon' => 'check-circle',
            ]));

            $timeline = $timeline->sortByDesc('date')->values();

            // 6. Advanced Insights
            $totalDays = $allLogs->where('type', 'actual')->count();
            $punctuality = $totalDays > 0 ? round((($totalDays - $lates) / $totalDays) * 100) : 100;

            // Official School Schedule
            $schoolSchedule = null;
            if ($intern->school) {
                $schoolSchedule = Schedule::where('type', 'school_default')
                    ->where('school_name', $intern->school)
                    ->first();
            }

            // Est. Completion (Assume 8h per day average)
            $remainingHours = max(0, $requiredHours - $renderedHours);
            $estDaysRemaining = ceil($remainingHours / 8);

            // Last Activity
            $lastEvent = $timeline->first();
            $lastActivityLabel = $lastEvent ? $lastEvent['date']->diffForHumans() : 'No activity yet';

            // Training Health Status
            $healthStatus = 'on-track';
            $healthLabel = 'Good Standing';
            if ($absents > 3 || ($totalTasks > 0 && ($completedTasks / $totalTasks) < 0.5)) {
                $healthStatus = 'at-risk';
                $healthLabel = 'Action Needed';
            } elseif ($renderedHours >= $requiredHours * 0.9) {
                $healthStatus = 'excellent';
                $healthLabel = 'Almost Finished';
            }

            return [
                'intern' => $intern,
                'supervisor' => $supervisor,
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'absents' => $absents,
                'lates' => $lates,
                'excused' => $excused,
                'renderedHours' => $renderedHours,
                'requiredHours' => $requiredHours,
                'attendanceHistory' => $allLogs,
                'journals' => $intern->journals()->latest()->get(),
                'timeline' => $timeline,
                'schoolSchedule' => $schoolSchedule,
                'insights' => [
                    'punctuality' => $punctuality,
                    'estDaysRemaining' => $estDaysRemaining,
                    'healthStatus' => $healthStatus,
                    'healthLabel' => $healthLabel,
                    'lastActivity' => $lastActivityLabel,
                ],
            ];
        });

        return view('coordinator.intern-show', $data);
    }
}
