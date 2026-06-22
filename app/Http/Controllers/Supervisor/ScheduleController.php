<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    public function index(AttendanceService $attendanceService)
    {
        $user = Auth::user();

        $portalType = 'Supervisor';
        $userName = $user->supervisorDetails->first_name ?? $user->name;
        $userRole = $user->supervisorDetails->office->name ?? 'Supervisor';
        $officeId = $user->supervisorDetails->office_id;

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
        $schedules = Schedule::whereNotNull('date')->orderBy('date', 'desc')->get();
        foreach ($schedules as $item) {
            $dateStr = $item->date->toDateString();
            $eventsByDate[$dateStr] = [
                [
                    'id' => $item->getRouteKey(),
                    'type' => $item->type,
                    'description' => $item->description,
                ],
            ];
        }

        // 3. Add Absence Requests from interns in this office
        $internIds = User::whereHas('internDetails', function ($query) use ($officeId) {
            $query->where('office_id', $officeId);
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

        $schedulesJson = json_encode($eventsByDate);

        return view('shared.schedule-read-only', compact('portalType', 'userName', 'userRole', 'schedulesJson'));
    }

    public function showAttachment(AbsenceRequest $absence, Request $request)
    {
        $user = Auth::user();
        $officeId = $user->supervisorDetails->office_id;

        // Verify the intern is in the supervisor's office
        if ($absence->intern->internDetails->office_id !== $officeId) {
            abort(403);
        }

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
}
