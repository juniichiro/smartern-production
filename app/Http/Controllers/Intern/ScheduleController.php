<?php

namespace App\Http\Controllers\Intern;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\Schedule;
use App\Models\Task;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    public function index(Request $request, AttendanceService $attendanceService, \App\Services\ScheduleMonthBuilder $monthBuilder)
    {
        $user = Auth::user();

        $data = $monthBuilder->build(
            $user,
            $request->query('month'),
            $request->query('view'),
            $request->query('date'),
            $attendanceService
        );

        return view('intern.schedule', $data);
    }

    public function storeAbsenceRequest(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // MB Limit
        ]);

        $user = Auth::user();
        $date = Carbon::parse($validated['date']);
        $type = $date->isToday() ? 'early_leave' : 'absence';

        // Check for existing request on this date
        $exists = AbsenceRequest::where('intern_id', $user->id)
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Request already submitted for this date.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('absences', 'public');
        }

        AbsenceRequest::create([
            'intern_id' => $user->id,
            'date' => $validated['date'],
            'type' => $type,
            'reason' => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
        ]);

        $msg = $type === 'early_leave' ? 'Early leave request finalized & submitted.' : 'Absence request finalized & submitted.';

        return back()->with('success', $msg);
    }

    public function updateAbsenceRequest(Request $request, AbsenceRequest $absence)
    {
        $this->authorize('update', $absence);

        if ($absence->status !== 'pending') {
            return back()->with('error', 'You can only modify pending requests.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $updateData = ['reason' => $validated['reason']];

        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($absence->attachment_path) {
                Storage::disk('public')->delete($absence->attachment_path);
            }
            $updateData['attachment_path'] = $request->file('attachment')->store('absences', 'public');
        }

        $absence->update($updateData);

        return back()->with('success', 'Absence request refined.');
    }

    public function destroyAbsenceRequest(AbsenceRequest $absence)
    {
        $this->authorize('delete', $absence);

        if ($absence->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be revoked.');
        }

        if ($absence->attachment_path) {
            Storage::disk('public')->delete($absence->attachment_path);
        }

        $absence->delete();

        return back()->with('success', 'Absence request withdrawn.');
    }

    public function showAttachment(AbsenceRequest $absence, Request $request)
    {
        // Allow intern (owner) or supervisors to access
        $user = Auth::user();
        $isOwner = $user->id === $absence->intern_id;
        $isManagement = in_array($user->role->name, ['supervisor', 'head_supervisor']);

        if (! $isOwner && ! $isManagement) {
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

    public function storeExtensionRequest(Request $request)
    {
        $request->validate([
            'new_end_date' => 'required|date|after:today',
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $intern = $user->internDetails;

        if (!$intern) {
            return back()->with('error', 'Intern details not found.');
        }

        $existing = \App\Models\InternExtension::where('intern_detail_id', $intern->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'You already have a pending extension request.');
        }

        $hasApproved = \App\Models\InternExtension::where('intern_detail_id', $intern->id)
            ->where('status', 'approved')
            ->exists();

        if ($hasApproved) {
            return back()->with('error', 'You have already been granted a date extension. Further extensions are not allowed.');
        }

        \App\Models\InternExtension::create([
            'intern_detail_id' => $intern->id,
            'previous_end_date' => $intern->end_date ?? $intern->start_date ?? now(),
            'new_end_date' => $request->input('new_end_date'),
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        // Find Head Supervisors and notify them
        $headSupervisorRole = \App\Models\Role::where('name', 'head_supervisor')->first();
        if ($headSupervisorRole) {
            $headSupervisors = \App\Models\User::where('role_id', $headSupervisorRole->id)->get();
            foreach ($headSupervisors as $hs) {
                \App\Http\Controllers\NotificationController::send(
                    $hs->id,
                    'Date Extension Requested',
                    'system',
                    "{$intern->first_name} {$intern->last_name} has requested a date extension to " . \Carbon\Carbon::parse($request->input('new_end_date'))->format('M d, Y') . ".",
                    route('head-supervisor.interns.show', $intern->user->getRouteKey())
                );
            }
        }

        return back()->with('success', 'Date extension requested successfully.');
    }
}
