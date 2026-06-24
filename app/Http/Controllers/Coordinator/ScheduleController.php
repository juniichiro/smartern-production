<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\AbsenceRequest;
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

        // 3. Add Absence Requests of interns from this coordinator's school
        if ($coordinator && $coordinator->school) {
            $school = $coordinator->school;
            $internIds = User::whereHas('internDetails', function ($query) use ($school) {
                $query->where('school', $school);
            })->pluck('id');

            $absences = AbsenceRequest::with('intern.internDetails')->whereIn('intern_id', $internIds)->get();
            foreach ($absences as $absence) {
                $dateStr = $absence->date->toDateString();
                $eventsByDate[$dateStr][] = [
                    'id' => $absence->getRouteKey(),
                    'type' => 'absence_request',
                    'request_type' => $absence->type,
                    'intern_name' => $absence->intern->internDetails->full_name ?? $absence->intern->name,
                    'reason' => $absence->reason,
                    'status' => $absence->status,
                    'remarks' => $absence->supervisor_remarks,
                    'has_attachment' => (bool) $absence->attachment_path,
                ];
            }
        }

        $schedulesJson = json_encode($eventsByDate);

        return view('shared.schedule-read-only', compact('portalType', 'userName', 'userRole', 'schedulesJson'));
    }
}
