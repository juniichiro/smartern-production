<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\InternDetail;
use App\Models\SupervisorDetail;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        // Fetch the single intern record for the authenticated user[cite: 8]
        $intern = InternDetail::where('user_id', Auth::id())
            ->with(['user', 'office', 'certificate'])
            ->firstOrFail();

        // Separately fetch the supervisor assigned to this office
        $supervisor = SupervisorDetail::where('office_id', $intern->office_id)
            ->with('user')
            ->first();

        // Metric calculations[cite: 7]
        $totalTasks = $intern->tasks->count();
        $completedTasks = $intern->tasks->where('status', 'completed')->count();
        $absents = $intern->attendances->where('status', 'absent')->count();

        // Training constants
        $requiredHours = $intern->required_hours ?? 480;
        $renderedHours = $intern->hours_rendered ?? 0;

        $totalAttendance = $intern->attendances->count();
        $onTimeCount = $intern->attendances->filter(fn ($a) => ! in_array($a->status, ['absent', 'late']))->count();
        $onTimePercentage = $totalAttendance > 0 ? round(($onTimeCount / $totalAttendance) * 100) : 100;

        return view('intern.profile', compact(
            'intern',
            'supervisor',
            'totalTasks',
            'completedTasks',
            'absents',
            'renderedHours',
            'requiredHours',
            'onTimePercentage'
        ));
    }

    public function updateAvatar(\Illuminate\Http\Request $request)
    {
        $rules = [
            'profile_picture' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];

        $messages = [
            'profile_picture.required' => 'Please select an image file to upload.',
            'profile_picture.image' => 'Please upload a valid image file.',
            'profile_picture.mimes' => 'Only JPG, JPEG, and PNG image formats are supported.',
            'profile_picture.max' => 'The image is too large. Please upload a photo smaller than 2MB.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first('profile_picture'));
        }

        $intern = InternDetail::where('user_id', Auth::id())->firstOrFail();

        // Delete old profile picture if exists
        if ($intern->profile_picture) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($intern->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile-pictures', 'public');
        
        $intern->update([
            'profile_picture' => $path,
        ]);

        return back()->with('success', 'Your profile photo has been updated.');
    }
}
