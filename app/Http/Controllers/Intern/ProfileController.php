<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\InternDetail;
use App\Models\SupervisorDetail;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        // Fetch the single intern record for the authenticated user[cite: 8]
        $intern = InternDetail::where('user_id', Auth::id())
            ->with(['user', 'office', 'certificate'])
            ->firstOrFail();

        // Separately fetch the supervisor assigned to this office
        $supervisor = SupervisorDetail::where('office_id', $intern->office_id)
            ->with('user')
            ->first();

        // Metric calculations[cite: 7]
        $totalTasks = $intern->tasks->count();
        $completedTasks = $intern->tasks->where('status', 'completed')->count();
        $absents = $intern->attendances->where('status', 'absent')->count();

        // Training constants
        $requiredHours = $intern->required_hours ?? 480;
        $renderedHours = $intern->hours_rendered ?? 0;

        $totalAttendance = $intern->attendances->count();
        $onTimeCount = $intern->attendances->filter(fn ($a) => ! in_array($a->status, ['absent', 'late']))->count();
        $onTimePercentage = $totalAttendance > 0 ? round(($onTimeCount / $totalAttendance) * 100) : 100;

        return view('intern.profile', compact(
            'intern',
            'supervisor',
            'totalTasks',
            'completedTasks',
            'absents',
            'renderedHours',
            'requiredHours',
            'onTimePercentage'
        ));
    }
}
