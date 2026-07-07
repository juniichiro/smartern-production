<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Display the intern's certificate preview page
     */
    public function index()
    {
        // Fetch the latest certificate belonging to the logged-in intern
        // Eager load both potential relationships to ensure data availability
        $certificate = Certificate::where('intern_id', Auth::id())
            ->with(['intern', 'internDetails'])
            ->latest()
            ->first();

        return view('intern.certificate', compact('certificate'));
    }

    /**
     * Export and Download the PDF for the intern
     */
    public function download(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        if (Auth::user()->role_id === \App\Enums\UserRole::INTERN) {
            abort(403, 'Interns are not permitted to download their certificate. Please coordinate with the Head Supervisor, Ms. Michelle A. Mal-in, to pick up your physical copy.');
        }

        if ($certificate->status !== 'published') {
            abort(403, 'Certificate is not yet published.');
        }

        // Apply the robust name-pulling logic
        $intern = $certificate->internDetails ?? $certificate->intern;
        $internFullName = $intern
            ? trim($intern->first_name.' '.$intern->last_name)
            : 'Unknown Intern';

        // Prepare data for the PDF template
        $data = [
            'intern' => (object) [
                'name' => $internFullName,
                'course' => $intern->course ?? 'Information Technology',
                'school' => $intern->school ?? 'FEU-TECH',
                'total_hours' => $certificate->completed_hours,
                'start_date' => $certificate->start_date,
                'end_date' => $certificate->end_date,
                'unit' => $certificate->intern_unit ?? 'Human Resource Teaching Unit',
            ],
            'date' => $certificate->issue_date ?? now(),
            'verificationCode' => $certificate->verification_code,
            'template' => $template = \App\Models\CertificateTemplate::getActive(),
        ];

        $pdf = Pdf::loadView('certificates.temp-certificate', $data);

        // Use the exact rendering options established for the supervisor side[cite: one, seven]
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
            'fontDir' => storage_path('fonts'),      // Permanent home for font files
            'fontCache' => storage_path('fonts'),    // Permanent home for .ufm metrics
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ]);

        $pdf->setPaper('letter', 'portrait');

        $customFileName = $internFullName.' - Certificate of Completion.pdf';

        return $pdf->download($customFileName);
    }
}
