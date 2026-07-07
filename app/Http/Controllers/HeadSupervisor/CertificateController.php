<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Display all certificates (system-wide)
     */
    public function index()
    {
        $certificates = Certificate::with(['intern'])
            ->latest()
            ->paginate(10);

        $stats = [
            'pending' => Certificate::where('verification_status', 'pending')->count(),
            'published' => Certificate::where('status', 'published')->count(),
        ];

        return view('head-supervisor.certificates', compact('certificates', 'stats'));
    }

    /**
     * Display all ready interns for endorsement (system-wide)
     */
    public function endorsements()
    {
        $interns = InternDetail::whereColumn('hours_rendered', '>=', 'required_hours')
            ->whereDoesntHave('certificate')
            ->with(['user', 'office'])
            ->get();

        return view('head-supervisor.endorsements', compact('interns'));
    }

    public function show(Certificate $certificate)
    {
        return view('head-supervisor.certificate-show', compact('certificate'));
    }

    public function publish(Certificate $certificate)
    {
        $userRole = auth()->user()->role;
        $roleName = is_object($userRole) ? ($userRole->name ?? '') :
                   (is_array($userRole) ? ($userRole['name'] ?? '') : $userRole);

        $validRoles = ['head_supervisor', 'head-supervisor', 'supervisor'];

        if (! in_array($roleName, $validRoles)) {
            abort(403, 'Unauthorized action.');
        }

        if ($certificate->status !== 'pending') {
            return back()->with('error', 'Only pending certificates can be published.');
        }

        $verificationCode = strtoupper(Str::random(12));

        $certificate->update([
            'status' => 'published',
            'verification_status' => 'verified',
            'verification_code' => $verificationCode,
            'published_at' => now(),
            'head_supervisor_id' => auth()->id(),
            'issue_date' => now(),
        ]);

        return redirect()->route('head-supervisor.certificates.index')
            ->with('success', 'Certificate finalized & published.');
    }

    public function download(Certificate $certificate)
    {
        if ($certificate->status !== 'published') {
            return back()->with('error', 'Only published certificates can be exported.');
        }

        $intern = $certificate->internDetails ?? $certificate->intern;
        $internFullName = $intern
            ? trim($intern->first_name.' '.$intern->last_name)
            : 'Unknown Intern';

        $template = \App\Models\CertificateTemplate::getActive();

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
            'template' => $template,
        ];

        $pdf = Pdf::loadView('certificates.temp-certificate', $data);

        // CONFIGURATION FOR LOCAL FONTS AND SYSTEM PATHS
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
            'fontDir' => storage_path('fonts'),      // Permanent home for font files
            'fontCache' => storage_path('fonts'),    // Permanent home for .ufm metrics
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ]);

        $pdf->setPaper('letter', 'portrait');

        $safeName = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $internFullName);
        $customFileName = trim($safeName).' - Certificate of Completion.pdf';

        return $pdf->download($customFileName);
    }

    public function verifyPublic($code)
    {
        $certificate = Certificate::where('verification_code', strtoupper($code))
            ->where('status', 'published')
            ->with(['intern', 'headSupervisor'])
            ->first();

        if (! $certificate) {
            return view('certificates.verification-failed');
        }

        return view('certificates.verification-success', compact('certificate'));
    }

    /**
     * Show the page to edit the certificate template
     */
    public function editTemplate()
    {
        $template = \App\Models\CertificateTemplate::getActive();
        return view('head-supervisor.certificate-template', compact('template'));
    }

    /**
     * Update/upload the certificate template
     */
    public function updateTemplate(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|max:2048',
            'footer' => 'nullable|image|max:4096',
            'gov_text' => 'required|string|max:255',
            'dept_text' => 'required|string|max:255',
            'region_text' => 'required|string|max:255',
            'office_text' => 'required|string|max:255',
            'title_text' => 'required|string|max:255',
            'body_text' => 'required|string',
            'signatory_name' => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
            'initials' => 'required|string|max:255',
        ]);

        $template = \App\Models\CertificateTemplate::latest()->first() ?? new \App\Models\CertificateTemplate();

        $data = $request->only([
            'gov_text',
            'dept_text',
            'region_text',
            'office_text',
            'title_text',
            'body_text',
            'signatory_name',
            'signatory_title',
            'initials',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('templates', 'public');
        }

        if ($request->hasFile('footer')) {
            $data['footer_path'] = $request->file('footer')->store('templates', 'public');
        }

        if ($template->exists) {
            $template->update($data);
        } else {
            \App\Models\CertificateTemplate::create($data);
        }

        return redirect()->route('head-supervisor.certificates.template.edit')
            ->with('success', 'Certificate template updated successfully.');
    }

    /**
     * Update the certificate metadata snapshot details directly (e.g. in case of system glitches)
     */
    public function updateDetails(\Illuminate\Http\Request $request, Certificate $certificate)
    {
        $request->validate([
            'intern_name' => 'required|string|max:255',
            'intern_course' => 'required|string|max:255',
            'intern_school' => 'required|string|max:255',
            'completed_hours' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'intern_unit' => 'required|string|max:255',
        ]);

        $certificate->update($request->only([
            'intern_name',
            'intern_course',
            'intern_school',
            'completed_hours',
            'start_date',
            'end_date',
            'intern_unit',
        ]));

        return back()->with('success', 'Certificate information corrected successfully.');
    }
}
