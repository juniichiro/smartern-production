<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function viewFile(Submission $submission)
    {
        $user = Auth::user();
        $roleName = $user->role->name;

        // Authorization checks:
        // 1. Interns can only view their own submissions.
        if ($roleName === 'intern' && $submission->intern_id !== $user->id) {
            abort(403, 'Unauthorized access to this submission.');
        }

        // 2. School coordinators can only view submissions of interns from their school.
        if ($roleName === 'school_coordinator') {
            $coordinatorSchool = $user->schoolCoordinatorDetails->school ?? null;
            if ($submission->intern->internDetails->school !== $coordinatorSchool) {
                abort(403, 'Unauthorized access to this student\'s submission.');
            }
        }

        // 3. Supervisors can only view submissions of interns in their office (if they are normal supervisors)
        if ($roleName === 'supervisor') {
            $officeId = $user->supervisorDetails->office_id ?? null;
            if ($submission->intern->internDetails->office_id !== $officeId) {
                abort(403, 'Unauthorized access to this office\'s submission.');
            }
        }

        // Validate the path
        $filePath = $submission->file_path;
        if (! $filePath) {
            abort(404, 'No file attached to this submission.');
        }

        // Resolve file location.
        // We support both:
        // - Relative storage path (e.g. "tasks/filename.ext" on public disk)
        // - Public disk prefixed path (e.g. "storage/tasks/filename.ext")
        // - Raw public folder path (e.g. "tasks/filename.ext" in public_path)

        // Clean path to get relative storage disk path
        $cleanStoragePath = str_replace('storage/', '', $filePath);

        if (Storage::disk('public')->exists($cleanStoragePath)) {
            $path = Storage::disk('public')->path($cleanStoragePath);
            return response()->file($path);
        }

        // Fallback: Check public_path for older files (direct public directory uploads)
        if (file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'File not found on disk.');
    }
}
