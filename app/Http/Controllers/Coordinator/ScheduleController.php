<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Display the global training schedule for the School Coordinator.
     */
    public function index(AttendanceService $attendanceService)
    {
        $user = Auth::user();
        $coordinator = $user->schoolCoordinatorDetails;

        $portalType = 'School Coordinator';
        $userName = $coordinator->first_name ?? $user->name;
        $userRole = 'School Coordinator'; // Or $coordinator->school

        // Fetch API holidays for current, prev, and next year
        $currentYear = now()->year;
        $apiHolidays = collect();
        for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++) {
            $apiHolidays = $apiHolidays->merge($attendanceService->getPhilippineHolidays($y));
        }

        $eventsByDate = [];

        // 1. Add API holidays
        foreach ($apiHolidays as $dateStr => $holiday) {
            $eventsByDate[$dateStr][] = [
                'type' => 'holiday',
                'description' => $holiday['description'],
            ];
        }

        // 2. Add database overrides (takes precedence/clears API holiday for the date)
        // These are global schedule changes (holidays, suspensions, weekend work)
        $schedules = Schedule::whereNotNull('date')->orderBy('date', 'desc')->get();
        foreach ($schedules as $item) {
            $dateStr = $item->date->toDateString();
            $eventsByDate[$dateStr] = [
                [
                    'id' => $item->id,
                    'type' => $item->type,
                    'description' => $item->description,
                ],
            ];
        }

        $schedulesJson = json_encode($eventsByDate);

        return view('shared.schedule-read-only', compact('portalType', 'userName', 'userRole', 'schedulesJson'));
    }
}
