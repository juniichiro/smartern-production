<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function reports()
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return view('supervisor.reports', [
                'activeInternsCount' => 0,
                'totalTasksCount' => 0,
                'completedTasksCount' => 0,
                'pendingTasksCount' => 0,
                'completionPercentage' => 0,
                'internPerformance' => collect([]),
                'months' => [],
                'completedTasksTrends' => [],
                'attendanceIssuesTrends' => [],
            ]);
        }

        $officeId = $supervisor->office_id;

        $internQuery = InternDetail::where('office_id', $officeId);
        $taskQuery = Task::where('office_id', $officeId);

        $activeInternsCount = (clone $internQuery)->count();
        $totalTasksCount = (clone $taskQuery)->count();
        $completedTasksCount = (clone $taskQuery)->where('status', 'completed')->count();
        $pendingTasksCount = (clone $taskQuery)->where('status', 'pending')->count();

        $completionPercentage = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100) : 0;
        $internPerformance = $this->getInternPerformance($user);

        // Generate dynamic labels for the last 6 months
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->format('M');
        })->values()->toArray();

        // Real Trend Data: Monthly Completed Tasks for this office
        $completedTasksTrends = collect(range(5, 0))->map(function ($i) use ($officeId) {
            $date = now()->subMonths($i);

            return Task::where('office_id', $officeId)
                ->where('status', 'completed')
                ->whereMonth('updated_at', $date->month)
                ->whereYear('updated_at', $date->year)
                ->count();
        })->values()->toArray();

        // Real Trend Data: Monthly Attendance Issues (lates/absents) for this office
        $attendanceIssuesTrends = collect(range(5, 0))->map(function ($i) use ($officeId) {
            $date = now()->subMonths($i);

            return Attendance::where('office_id', $officeId)
                ->whereIn('status', ['late', 'absent'])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        })->values()->toArray();

        return view('supervisor.reports', compact(
            'activeInternsCount',
            'totalTasksCount',
            'completedTasksCount',
            'pendingTasksCount',
            'completionPercentage',
            'internPerformance',
            'months',
            'completedTasksTrends',
            'attendanceIssuesTrends'
        ));
    }

    public function exportPDF()
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;
        $officeId = $supervisor->office_id;

        $taskQuery = Task::where('office_id', $officeId);

        $completedTasksCount = (clone $taskQuery)->where('status', 'completed')->count();
        $pendingTasksCount = (clone $taskQuery)->where('status', 'pending')->count();
        $internPerformance = $this->getInternPerformance($user);

        // Logo Base64 encoding
        $logoSrc = null;
        $logoPath = public_path('images/blue-smartern.png'); // Ensure this matches your actual file name
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,'.$logoData;
        }

        $officeName = $supervisor->office->name ?? 'Office';

        $pdf = Pdf::loadView('exports.reports-pdf', [
            'logo' => $logoSrc,
            'officeName' => $officeName,
            'date' => now()->format('F d, Y'),
            'interns' => $internPerformance,
            'completedTasksCount' => $completedTasksCount,
            'pendingTasksCount' => $pendingTasksCount,
        ]);

        return $pdf->setPaper('a4', 'portrait')->download('SmarTern_Report_'.now()->format('Y-m-d').'.pdf');
    }

    private function getInternPerformance($user)
    {
        $officeId = $user->supervisorDetails->office_id;
        $query = InternDetail::where('office_id', $officeId);

        return $query->withCount(['tasks as total_tasks'])
            ->withCount(['tasks as completed_tasks' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->get()
            ->map(function ($intern) {
                $intern->performance_rate = $intern->total_tasks > 0
                    ? ($intern->completed_tasks / $intern->total_tasks) * 100
                    : 0;

                return $intern;
            })
            ->sortByDesc('performance_rate');
    }
}
