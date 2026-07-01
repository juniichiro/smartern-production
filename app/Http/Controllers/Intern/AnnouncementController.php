<?php

namespace App\Http\Controllers\Intern;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of office announcements for the intern.
     */
    public function index()
    {
        $user = Auth::user();
        $officeId = $user->internDetails->office_id;

        // Fetch announcements matching the intern's office or global announcements (office_id is null)
        $announcements = Announcement::where(function ($query) use ($officeId) {
            $query->where('office_id', $officeId)
                  ->orWhereNull('office_id');
        })
            ->with('user.supervisorDetails')
            ->latest()
            ->paginate(9);

        return view('intern.announcements', compact('announcements'));
    }

    /**
     * Display the specified announcement.
     */
    public function show($announcementKey)
    {
        try {
            $id = Hashids::decode($announcementKey)[0] ?? null;
            $announcement = Announcement::with([
                'user.supervisorDetails.office',
            ])->find($id);

            if (! $announcement) {
                return redirect()->route('intern.announcements')
                    ->with('error', 'We couldn\'t find that announcement. It may have been withdrawn.');
            }

            $this->authorize('view', $announcement);

            return view('intern.announcements-show', compact('announcement'));

        } catch (\Exception $e) {
            return redirect()->route('intern.announcements')
                ->with('error', 'The announcement link is invalid or has expired.');
        }
    }
}
