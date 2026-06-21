<?php

namespace App\Http\Controllers\Intern;

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

        // Fetch announcements from supervisors in the same office
        $announcements = Announcement::whereHas('user.supervisorDetails', function ($q) use ($officeId) {
            $q->where('office_id', $officeId);
        })
            ->with('user.supervisorDetails')
            ->latest()
            ->paginate(12);

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
