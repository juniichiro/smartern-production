<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\InternDetail;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $coordinator = Auth::user()->schoolCoordinatorDetails;

        if (! $coordinator) {
            return view('coordinator.dashboard', [
                'stats' => ['total_interns' => 0, 'placed_interns' => 0, 'pending_placements' => 0, 'completed' => 0],
                'placements' => collect([]),
                'interns' => collect([]),
                'milestones' => ['average_progress' => 0],
            ]);
        }

        $school = $coordinator->school;
        $cacheKey = 'coordinator_dashboard_'.md5($school);

        $dashboardData = Cache::remember($cacheKey, 300, function () use ($school) {
            // Core cohort metrics
            $stats = [
                'total_interns' => InternDetail::where('school', $school)->count(),
                'placed_interns' => InternDetail::where('school', $school)->whereNotNull('office_id')->count(),
                'pending_placements' => InternDetail::where('school', $school)->whereNull('office_id')->count(),
                'completed' => Certificate::whereHas('intern', function ($query) use ($school) {
                    $query->where('school', $school);
                })->where('status', 'published')->count(),
            ];

            // SDO Division deployments with count of interns from THIS SCHOOL currently assigned
            $placements = Office::whereHas('internDetails', function ($query) use ($school) {
                $query->where('school', $school);
            })->withCount(['internDetails' => function ($query) use ($school) {
                $query->where('school', $school);
            }])->get();

            // SDO Delegation Registry
            $interns = InternDetail::with(['user', 'office', 'certificate'])
                ->where('school', $school)
                ->get();

            // Cohort Milestones
            $averageProgress = InternDetail::where('school', $school)
                ->selectRaw('AVG(hours_rendered / COALESCE(NULLIF(required_hours, 0), 480) * 100) as avg_prog')
                ->value('avg_prog') ?? 0;
            $averageProgress = min(100, round($averageProgress));

            $milestones = [
                'average_progress' => $averageProgress,
            ];

            return [
                'stats' => $stats,
                'placements' => $placements,
                'interns' => $interns,
                'milestones' => $milestones,
            ];
        });

        return view('coordinator.dashboard', $dashboardData);
    }
}
