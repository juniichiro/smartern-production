<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\InternDetail;
use App\Models\OnboardingApplicant;
use App\Models\Schedule;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    public function index(AttendanceService $attendanceService)
    {
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

        // 2. Add database overrides (date-based exceptions)
        $dateExceptions = Schedule::whereNotNull('date')->orderBy('date', 'desc')->get();
        foreach ($dateExceptions as $item) {
            $dateStr = $item->date->toDateString();
            $eventsByDate[$dateStr] = [
                [
                    'id' => $item->id,
                    'type' => $item->type,
                    'description' => $item->description,
                ],
            ];
        }

        // 3. Add all Absence Requests
        $absences = AbsenceRequest::with('intern.internDetails')->get();
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

        // 4. School-specific default schedules
        $schoolSchedules = Schedule::where('type', 'school_default')->orderBy('school_name', 'asc')->get()->map(function ($schedule) {
            $schedule->hashid = $schedule->getRouteKey();
            return $schedule;
        });

        // Fetch universities with active interns or onboarding applicants
        $internSchools = InternDetail::distinct()->pluck('school')->toArray();
        $applicantSchools = OnboardingApplicant::distinct()->pluck('university')->toArray();
        $universities = collect(array_merge($internSchools, $applicantSchools))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $schedulesJson = json_encode($eventsByDate);
        $schoolSchedulesJson = json_encode($schoolSchedules->keyBy('school_name'));

        return view('head-supervisor.schedule', compact('dateExceptions', 'schoolSchedules', 'schedulesJson', 'schoolSchedulesJson', 'universities'));
    }

    public function showAttachment(AbsenceRequest $absence, Request $request)
    {
        if (! $absence->attachment_path || ! Storage::disk('public')->exists($absence->attachment_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($absence->attachment_path);

        if ($request->has('download')) {
            return response()->download($path);
        }

        return response()->file($path);
    }

    public function reviewAbsenceRequest(Request $request, AbsenceRequest $absence)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'supervisor_remarks' => 'nullable|string|max:1000',
        ]);

        $absence->update([
            'status' => $validated['status'],
            'supervisor_remarks' => $validated['supervisor_remarks'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $statusLabel = $validated['status'] === 'approved' ? 'approved' : 'rejected';

        return back()->with('success', "Absence request {$statusLabel}.");
    }

    public function store(Request $request)
    {
        $isSchoolDefault = $request->type === 'school_default';

        if (! $isSchoolDefault) {
            if (Carbon::parse($request->date)->lt(today())) {
                return back()->withErrors(['date' => 'You cannot register schedule exceptions for dates that have already passed.']);
            }
        }

        $rules = [
            'type' => 'required|string|in:holiday,suspension,work_day,school_default',
            'description' => 'required_if:type,holiday,suspension,work_day|nullable|string|max:255',
        ];

        if ($isSchoolDefault) {
            $rules['school_name'] = 'required|string|unique:schedules,school_name';
            $rules['start_time'] = 'required';
            $rules['end_time'] = 'required';
            $rules['daily_hours'] = 'required|integer|min:0|max:24';
            $rules['rounding_mode'] = 'required|string|in:none,floor,ceil';
            $rules['late_policy'] = 'required|string|in:actual,next_hour,next_half_hour';
        } else {
            $rules['date'] = 'required|date|unique:schedules,date';
        }

        $validated = $request->validate($rules, [
            'date.unique' => 'A schedule exception has already been registered for this date.',
            'school_name.unique' => 'A default schedule has already been registered for this school.',
        ]);

        if ($isSchoolDefault) {
            $validated['description'] = "Default schedule for {$request->school_name}";
        }

        Schedule::create($validated);

        $message = $isSchoolDefault ? 'School default schedule finalized.' : 'Schedule exception finalized.';

        return redirect()->route('head-supervisor.schedule.index')
            ->with('success', $message);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $isSchoolDefault = $schedule->type === 'school_default';

        if (! $isSchoolDefault) {
            if ($schedule->date->lt(today())) {
                return back()->withErrors(['date' => 'You cannot update schedule exceptions for dates that have already passed.']);
            }
            if (Carbon::parse($request->date)->lt(today())) {
                return back()->withErrors(['date' => 'You cannot move a schedule exception to a date that has already passed.']);
            }
        }

        $rules = [
            'type' => 'required|string|in:holiday,suspension,work_day,school_default',
            'description' => 'required_if:type,holiday,suspension,work_day|nullable|string|max:255',
        ];

        if ($isSchoolDefault) {
            $rules['school_name'] = 'required|string|unique:schedules,school_name,'.$schedule->id;
            $rules['start_date'] = 'nullable|date';
            $rules['target_end_date'] = 'nullable|date|after:start_date';

            $rules['start_time'] = 'required';
            $rules['end_time'] = 'required';
            $rules['daily_hours'] = 'required|integer|min:0|max:24';
            $rules['rounding_mode'] = 'required|string|in:none,floor,ceil';
            $rules['late_policy'] = 'required|string|in:actual,next_hour,next_half_hour';
        } else {
            $rules['date'] = 'required|date|unique:schedules,date,'.$schedule->id;
        }

        $validated = $request->validate($rules);

        if ($isSchoolDefault) {
            $validated['description'] = "Default schedule for {$request->school_name}";
        }

        $schedule->update($validated);

        return redirect()->route('head-supervisor.schedule.index')
            ->with('success', 'Schedule details refined.');
    }

    public function destroy(Schedule $schedule)
    {
        if ($schedule->type !== 'school_default' && $schedule->date->lt(today())) {
            return back()->withErrors(['date' => 'You cannot withdraw schedule exceptions for dates that have already passed.']);
        }

        $schedule->delete();

        return redirect()->route('head-supervisor.schedule.index')
            ->with('success', 'Schedule exception withdrawn.');
    }
}
