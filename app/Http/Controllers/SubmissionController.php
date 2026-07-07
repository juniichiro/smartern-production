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

        // Authorization checks
        // Interns can only view their own submissions
        if ($roleName === 'intern' && $submission->intern_id !== $user->id) {
            abort(403, "Unauthorized: You are logged in as an intern but this submission belongs to intern ID {$submission->intern_id} (your ID is {$user->id}).");
        }

        // School coordinators can only view submissions of interns from their school
        if ($roleName === 'school_coordinator') {
            $coordinatorSchool = $user->schoolCoordinatorDetails->school ?? null;
            $internSchool = $submission->intern->internDetails->school ?? null;
            if ($internSchool !== $coordinatorSchool) {
                abort(403, "Unauthorized: Coordinator school '{$coordinatorSchool}' does not match intern school '{$internSchool}'.");
            }
        }

        // Supervisors and Head Supervisors can only view submissions of interns in their office
        if ($roleName === 'supervisor' || $roleName === 'head_supervisor') {
            $officeId = $user->supervisorDetails->office_id ?? null;
            $internOfficeId = $submission->intern->internDetails->office_id ?? null;
            if ($internOfficeId !== $officeId) {
                abort(403, "Unauthorized: Office ID mismatch (User office: '{$officeId}', Intern office: '{$internOfficeId}').");
            }
        }

        // Validate the path
        $filePath = $submission->file_path;
        if (! $filePath) {
            abort(404, 'No file attached to this submission.');
        }

        // Resolve file location
        // We support both
        // Relative storage path (e.g. "tasks/filename.ext" on public disk)
        // Public disk prefixed path (e.g. "storage/tasks/filename.ext")
        // Raw public folder path (e.g. "tasks/filename.ext" in public_path)

        // Clean path to get relative storage disk path
        $cleanStoragePath = str_replace('storage/', '', $filePath);

        if (Storage::disk('public')->exists($cleanStoragePath)) {
            return Storage::disk('public')->response($cleanStoragePath);
        }

        // Fallback: Check public_path for older files (direct public directory uploads)
        if (file_exists(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, "File not found on disk (tried storage: '{$cleanStoragePath}' and public: '{$filePath}').");
    }
}
