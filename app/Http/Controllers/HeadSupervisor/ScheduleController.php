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
use Illuminate\Validation\Rule;

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
            $eventsByDate[$dateStr][] = [
                'id' => $item->getRouteKey(),
                'type' => $item->type,
                'description' => $item->description,
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
                'attachment_extension' => $absence->attachment_path ? strtolower(pathinfo($absence->attachment_path, PATHINFO_EXTENSION)) : null,
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

        $interns = InternDetail::with('user')->orderBy('last_name', 'asc')->get();
        $internsJson = json_encode($interns->map(function ($i) {
            return [
                'id' => $i->user->getRouteKey(),
                'name' => "{$i->first_name} {$i->last_name}",
                'email' => $i->user->email ?? '',
                'schedule_assignment' => $i->schedule_assignment ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'required_hours' => $i->required_hours ?? 480,
            ];
        }));

        return view('head-supervisor.schedule', compact('dateExceptions', 'schoolSchedules', 'schedulesJson', 'schoolSchedulesJson', 'universities', 'internsJson'));
    }

    public function showAttachment(AbsenceRequest $absence, Request $request)
    {
        if (! $absence->attachment_path || ! Storage::disk('public')->exists($absence->attachment_path)) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if ($request->has('download')) {
            return $disk->download($absence->attachment_path);
        }

        $mime = Storage::disk('public')->mimeType($absence->attachment_path);
        return $disk->response($absence->attachment_path, null, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($absence->attachment_path) . '"',
        ]);
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
            $rules['school_name'] = [
                'required',
                'string',
                Rule::unique('schedules', 'school_name')->where(fn ($q) => $q->where('type', 'school_default'))
            ];
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

    public function update(Request $request, $schedule)
    {
        if (!($schedule instanceof Schedule)) {
            $schedule = (new Schedule)->resolveRouteBinding($schedule);
        }

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
            $rules['school_name'] = 'required|string|unique:schedules,school_name,' . $schedule->id . ',id,type,school_default';
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

        $validated = $request->validate($rules, [
            'school_name.unique' => 'A default schedule has already been registered for this school.',
        ]);

        if ($isSchoolDefault) {
            $validated['description'] = "Default schedule for {$request->school_name}";
        }

        $schedule->update($validated);

        return redirect()->route('head-supervisor.schedule.index')
            ->with('success', 'Schedule details refined.');
    }

    public function destroy(Schedule $schedule)
    {
        if ($schedule->type !== 'school_default' && $schedule->date->lt(today()) && ! $schedule->date->isToday()) {
            return back()->withErrors(['date' => 'You cannot withdraw schedule exceptions for dates that have already passed.']);
        }

        if ($schedule->type !== 'school_default') {
            Schedule::whereDate('date', $schedule->date->toDateString())
                ->where('type', '!=', 'school_default')
                ->delete();
        } else {
            $schedule->delete();
        }

        return redirect()->route('head-supervisor.schedule.index')
            ->with('success', 'Schedule exception withdrawn.');
    }
}
