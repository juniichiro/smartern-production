<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(AttendanceService $attendanceService)
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return view('supervisor.attendance', [
                'attendances' => collect([]),
                'todayCount' => 0,
                'activeCount' => 0,
                'weeklyHours' => 0,
                'attendanceRate' => 0,
            ]);
        }

        $officeId = $supervisor->office_id;

        $query = InternDetail::query();
        $query->where('office_id', $officeId);

        // Fetch active interns in this office
        $activeInterns = $query->whereDoesntHave('certificate', function ($query) {
            $query->where('status', 'published');
        })
            ->with(['user'])
            ->get();

        // Accumulate merged logs for active interns
        $allLogs = collect();
        foreach ($activeInterns as $intern) {
            $internLogs = $attendanceService->getMergedLogs($intern->user);
            foreach ($internLogs as $log) {
                $log['intern'] = $intern;
                $allLogs->push($log);
            }
        }

        // Sort merged master registry logs by date/clock_in descending
        $sortedLogs = $allLogs->sortByDesc(function ($item) {
            return $item['clock_in'] ? $item['clock_in']->timestamp : $item['date']->timestamp;
        });

        // Apply filters in backend for timeline display
        $search = request('search');
        $tab = request('tab', 'all');
        $filteredLogs = $sortedLogs;

        if ($search) {
            $searchLower = strtolower($search);
            $filteredLogs = $filteredLogs->filter(function ($log) use ($searchLower) {
                $logIntern = $log['intern'];
                return str_contains(strtolower($logIntern->first_name . ' ' . $logIntern->last_name), $searchLower) ||
                       str_contains(strtolower($logIntern->student_id), $searchLower);
            });
        }

        if ($tab !== 'all') {
            if ($tab === 'active') {
                $filteredLogs = $filteredLogs->filter(function ($log) {
                    return $log['type'] === 'actual' && empty($log['clock_out']) && $log['clock_in'] && $log['clock_in']->isToday();
                });
            } elseif ($tab === 'exceptions') {
                $filteredLogs = $filteredLogs->filter(function ($log) {
                    return $log['status'] === 'late' || $log['status'] === 'absent';
                });
            } elseif ($tab === 'offdays') {
                $filteredLogs = $filteredLogs->filter(function ($log) {
                    return $log['status'] === 'holiday' || $log['status'] === 'suspension';
                });
            }
        }

        // Paginate the filtered logs
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $filteredLogs->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $filteredLogs->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        // Keep a copy of sorted logs for summary cards, unique interns list, and drawer
        $allLogs = $sortedLogs;

        // Stats for the cards (restricted to current office)
        $todayCount = Attendance::where('office_id', $officeId)->whereDate('clock_in', now())->count();

        $activeCount = Attendance::where('office_id', $officeId)
            ->whereDate('clock_in', now())
            ->whereNull('clock_out')
            ->count();

        // Weekly Hours Calculation
        $weeklyLogsCount = Attendance::where('office_id', $officeId)
            ->whereBetween('clock_in', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotNull('clock_out')
            ->count();

        $weeklyHours = $weeklyLogsCount * 8;

        // Attendance Rate
        $internCount = InternDetail::where('office_id', $officeId)->count();

        $expectedHours = $internCount * 40;
        $attendanceRate = $expectedHours > 0 ? min(round(($weeklyHours / $expectedHours) * 100), 100) : 0;

        return view('supervisor.attendance', compact(
            'attendances',
            'allLogs',
            'todayCount',
            'activeCount',
            'weeklyHours',
            'attendanceRate'
        ));
    }
}
