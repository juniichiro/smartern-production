<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\InternDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Display the list of interns for endorsement
     */
    public function index()
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return view('supervisor.certificates', [
                'interns' => collect([]),
            ]);
        }

        $query = InternDetail::query();
        $officeId = $supervisor->office_id;
        $query->where('office_id', $officeId);

        // Eager load 'user' and singular 'certificate'
        $interns = $query->with(['user', 'certificate'])
            ->get();

        return view('supervisor.certificates', compact('interns'));
    }

    /**
     * Preview the certificate before submission
     */
    public function preview($certificate)
    {
        $intern = InternDetail::byPublicOrId($certificate)->firstOrFail();
        $user = Auth::user();

        // Ensure the intern is in the supervisor's office
        $officeId = $user->supervisorDetails->office_id;
        if ($intern->office_id !== $officeId) {
            abort(403, 'Unauthorized.');
        }

        $certificateRecord = Certificate::where('intern_id', $intern->user_id)->first();

        return view('supervisor.certificate-preview', compact('intern', 'certificateRecord'));
    }

    /**
     * Download a draft certificate PDF
     */
    public function download($certificate)
    {
        $intern = InternDetail::byPublicOrId($certificate)->firstOrFail();
        $user = Auth::user();

        // Ensure the intern is in the supervisor's office
        $officeId = $user->supervisorDetails->office_id;
        if ($intern->office_id !== $officeId) {
            abort(403, 'Unauthorized.');
        }

        $certificateRecord = Certificate::where('intern_id', $intern->user_id)->first();

        $internFullName = trim($intern->first_name.' '.$intern->last_name);

        $data = [
            'intern' => (object) [
                'name' => $internFullName,
                'course' => $intern->course ?? 'Information Technology',
                'school' => $intern->school ?? 'FEU-TECH',
                'total_hours' => $certificateRecord->completed_hours ?? $intern->hours_rendered ?? $intern->required_hours ?? 480,
                'start_date' => $certificateRecord->start_date ?? $intern->start_date,
                'end_date' => $certificateRecord->end_date ?? now(),
                'unit' => $certificateRecord->intern_unit ?? Auth::user()->supervisorDetails->office->name ?? 'Human Resource Teaching Unit',
            ],
            'date' => $certificateRecord->issue_date ?? now(),
            'verificationCode' => $certificateRecord->verification_code ?? 'DRAFT-'.strtoupper(Str::random(8)),
            'template' => \App\Models\CertificateTemplate::getActive(),
        ];

        $pdf = Pdf::loadView('certificates.temp-certificate', $data);

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts'),
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ]);

        $pdf->setPaper('letter', 'portrait');

        $safeName = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $internFullName);
        $customFileName = 'DRAFT - '.trim($safeName).' - Certificate of Completion.pdf';

        return $pdf->download($customFileName);
    }

    /**
     * Endorse an intern to the Head Supervisor
     * Note: $certificate here is the intern's user_id from the route
     */
    public function endorse(Request $request, $certificate)
    {
        $this->authorize('create', Certificate::class);

        // Find the intern using the ID passed through the {certificate} parameter
        $intern = InternDetail::byPublicOrId($certificate)->firstOrFail();

        // Eligibility Check: Ensure they have reached their required hours
        $reqHours = $intern->required_hours ?? 480;
        if (($intern->hours_rendered ?? 0) < $reqHours) {
            return redirect()->back()->with('error', 'Intern has not yet completed the required ' . $reqHours . ' hours.');
        }

        // Validate request
        $request->validate([
            'supervisor_feedback' => 'nullable|string|max:5000',
            'technical_proficiency' => 'required|string|in:Excellent,Very Good,Good,Fair,Poor',
            'professionalism_ethics' => 'required|string|in:Excellent,Very Good,Good,Fair,Poor',
            'communication_skills' => 'required|string|in:Excellent,Very Good,Good,Fair,Poor',
            'team_collaboration' => 'required|string|in:Excellent,Very Good,Good,Fair,Poor',
        ]);

        // Create or Update the Certificate record
        $cert = Certificate::updateOrCreate(
            ['intern_id' => $intern->user_id],
            [
                'supervisor_id' => auth()->id(),
                'status' => 'pending',
                'verification_status' => 'pending',
                'completed_hours' => $intern->hours_rendered, // Captures actual hours
                'start_date' => $intern->start_date,      // Captures start date
                'end_date' => now(),                    // End date of internship
                'intern_unit' => Auth::user()->supervisorDetails->office->name ?? 'N/A',
                'endorsed_at' => now(),
                'supervisor_feedback' => $request->input('supervisor_feedback'),
            ]
        );

        // Save the specific evaluations
        $cert->evaluation()->updateOrCreate(
            ['certificate_id' => $cert->certificate_id],
            [
                'technical_proficiency' => $request->input('technical_proficiency'),
                'professionalism_ethics' => $request->input('professionalism_ethics'),
                'communication_skills' => $request->input('communication_skills'),
                'team_collaboration' => $request->input('team_collaboration'),
            ]
        );

        return redirect()->route('supervisor.certificates.index')->with('success', "Endorsement finalized for {$intern->first_name}.");
    }

    /**
     * Helper verify method (same logic as endorse)
     */
    public function verify(Request $request, $certificate)
    {
        return $this->endorse($request, $certificate);
    }
}
