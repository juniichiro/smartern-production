<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\NotificationController;
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

        // Add API holidays
        foreach ($apiHolidays as $dateStr => $holiday) {
            $eventsByDate[$dateStr][] = [
                'type' => 'holiday',
                'description' => $holiday['description'],
            ];
        }

        // Add database overrides (takes precedence/clears API holiday for the date)
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

        // Add Absence Requests from interns in this office
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
                'attachment_extension' => $absence->attachment_path ? strtolower(pathinfo($absence->attachment_path, PATHINFO_EXTENSION)) : null,
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

        $reviewer = Auth::user();
        $statusLabel = $validated['status'] === 'approved' ? 'approved' : 'rejected';

        $absence->update([
            'status' => $validated['status'],
            'supervisor_remarks' => $validated['supervisor_remarks'],
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        // Notify Head Supervisors if reviewed by a standard supervisor
        if ($reviewer->role->name === 'supervisor') {
            $headSupervisorRole = Role::where('name', 'head_supervisor')->first();
            if ($headSupervisorRole) {
                $headSupervisors = User::where('role_id', $headSupervisorRole->id)->get();
                $internName = $absence->intern->internDetails->full_name ?? $absence->intern->name;
                $reviewerName = $reviewer->supervisorDetails->first_name ?? $reviewer->name;

                foreach ($headSupervisors as $headSupervisor) {
                    NotificationController::send(
                        $headSupervisor->id,
                        'Absence Request Reviewed',
                        'schedule',
                        "Supervisor {$reviewerName} has {$statusLabel} the absence request of intern {$internName}.",
                        route('head-supervisor.schedule.index')
                    );
                }
            }
        }

        // Notify School Coordinators if approved
        if ($validated['status'] === 'approved') {
            $internDetail = $absence->intern->internDetails;
            if ($internDetail && $internDetail->school) {
                $coordinators = \App\Models\SchoolCoordinatorDetail::getCoordinatorsForSchool($internDetail->school);
                $internName = $internDetail->full_name ?? $absence->intern->name;
                $requestTypeLabel = $absence->type === 'early_leave' ? 'Early Leave' : 'Absence';
                
                foreach ($coordinators as $coord) {
                    NotificationController::send(
                        $coord->user_id,
                        $requestTypeLabel . ' Request Approved',
                        'system',
                        "The {$requestTypeLabel} request for intern {$internName} ({$internDetail->school}) on " . $absence->date->format('M d, Y') . " has been approved.",
                        route('coordinator.schedule.index')
                    );
                }
            }
        }

        return back()->with('success', "Absence request {$statusLabel}.");
    }
}
