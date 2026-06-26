<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Requests\Supervisor\StoreAnnouncementRequest;
use App\Http\Requests\Supervisor\UpdateAnnouncementRequest;
use App\Mail\OfficeAnnouncementMail;
use App\Models\Announcement;
use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    protected $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $supervisor = Auth::user();

        // Ensure supervisor details are available
        if (! $supervisor->supervisorDetails) {
            return redirect()->route('dashboard')->with('error', 'Supervisor profile not found.');
        }

        $announcements = Announcement::where('user_id', $supervisor->id)
            ->latest()
            ->paginate(10);

        return view('supervisor.announcements', compact('announcements'));
    }

    public function show($announcementKey)
    {
        try {
            $id = \Vinkla\Hashids\Facades\Hashids::decode($announcementKey)[0] ?? null;
            $announcement = Announcement::with([
                'user.supervisorDetails.office',
            ])->find($id);

            if (! $announcement) {
                return redirect()->route('supervisor.announcements')
                    ->with('error', 'We couldn\'t find that announcement. It may have been withdrawn.');
            }

            return view('supervisor.announcements-show', compact('announcement'));

        } catch (\Exception $e) {
            return redirect()->route('supervisor.announcements')
                ->with('error', 'The announcement link is invalid or has expired.');
        }
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $filePath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('announcements', 'public');
            $filePath = 'storage/' . $path;
        }

        $announcement = Announcement::create([
            'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'attachment' => $filePath,
        ]);

        // Notify all interns in the same office (if applicable) or all interns if system-wide
        // For now, let's notify all interns in the supervisor's office
        $officeId = Auth::user()->supervisorDetails->office_id;
        $interns = User::whereHas('role', function ($q) {
            $q->where('name', 'intern');
        })->whereHas('internDetails', function ($q) use ($officeId) {
            $q->where('office_id', $officeId);
        })->get();

        foreach ($interns as $intern) {
            NotificationController::send(
                $intern->id,
                'Office Announcement',
                'announcement',
                "New Announcement: '{$announcement->title}'",
                route('intern.announcements.show', $announcement)
            );

            try {
                Mail::to($intern->email)->send(new OfficeAnnouncementMail($announcement, $intern));
            } catch (\Exception $e) {
                Log::error("Failed to send announcement email to {$intern->email}: ".$e->getMessage());
            }
        }

        $this->auditService->log('Announcement Created', "Title: '{$announcement->title}'");

        return redirect()->back()->with('success', 'Announcement published.');
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $validated = $request->validated();

        $filePath = $announcement->attachment;
        if ($request->hasFile('attachment')) {
            if ($announcement->attachment) {
                $relativeStoragePath = str_replace('storage/', '', $announcement->attachment);
                Storage::disk('public')->delete($relativeStoragePath);
            }
            $file = $request->file('attachment');
            $path = $file->store('announcements', 'public');
            $filePath = 'storage/' . $path;
        }

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'attachment' => $filePath,
        ]);

        // Notify all interns in the same office about the update
        $officeId = $announcement->user->supervisorDetails->office_id ?? null;
        if ($officeId) {
            $interns = User::whereHas('role', function ($q) {
                $q->where('name', 'intern');
            })->whereHas('internDetails', function ($q) use ($officeId) {
                $q->where('office_id', $officeId);
            })->get();

            foreach ($interns as $intern) {
                NotificationController::send(
                    $intern->id,
                    'Office Announcement',
                    'announcement',
                    "Updated Announcement: '{$announcement->title}'",
                    route('intern.announcements.show', $announcement)
                );

                try {
                    Mail::to($intern->email)->send(new OfficeAnnouncementMail($announcement, $intern));
                } catch (\Exception $e) {
                    Log::error("Failed to send updated announcement email to {$intern->email}: ".$e->getMessage());
                }
            }
        }

        $this->auditService->log('Announcement Updated', "Title: '{$announcement->title}' modified");

        return redirect()->back()->with('success', 'Announcement details refined.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);

        $title = $announcement->title;
        $announcement->delete();

        $this->auditService->log('Announcement Deleted', "Title: '{$title}' removed");

        return redirect()->back()->with('success', 'Announcement withdrawn.');
    }
}
