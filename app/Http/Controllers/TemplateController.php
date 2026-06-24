<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display all request templates.
     */
    public function index()
    {
        $templates = [
            [
                'id' => 'account_edit',
                'title' => 'Account Details Modification Form',
                'description' => 'Use this form to request formal correction or edits to your personal details, email, or institutional information (school/course).',
                'fields' => ['Full Name', 'Role & ID Number', 'Target Fields to Edit', 'Reason for Correction'],
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
            ],
            [
                'id' => 'account_unblock',
                'title' => 'Account Unblock & Restoration Request',
                'description' => 'Use this form to request the reactivation or restoration of your portal account in cases of automatic security lockouts, anomalies, or suspensions.',
                'fields' => ['Account Username/Email', 'Onboarding Status', 'Date of Lockout', 'Statement of Security Clearance'],
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><line x1="12" y1="15" x2="12" y2="17"/></svg>',
            ],
            [
                'id' => 'attendance_adjust',
                'title' => 'Weekly Attendance Adjustment Form',
                'description' => 'Use this form to request adjustments for log failures, schedule mismatches, or missing QR code check-in scans.',
                'fields' => ['Requested Adjustment Date', 'Rendered Session Hours', 'Missed Scan Reason', 'Office Supervisor Signature'],
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
            ],
            [
                'id' => 'mfa_reset',
                'title' => 'MFA Reset & Security Adjustment Request',
                'description' => 'Use this form to request a full reset of linked authenticator apps or 2FA email settings for your registered user account.',
                'fields' => ['Registered Email', 'Device Change Reason', 'Backup Code Availability', 'Identity Proof (ID Attachment)'],
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><rect width="8" height="5" x="8" y="11" rx="1"/><circle cx="12" cy="12" r="1"/></svg>',
            ]
        ];

        return view('templates.index', compact('templates'));
    }

    /**
     * Download the printable request form PDF.
     */
    public function download($type)
    {
        $templateData = match ($type) {
            'account_edit' => [
                'title' => 'ACCOUNT DETAILS MODIFICATION REQUEST',
                'form_code' => 'SMRT-FORM-001',
                'sections' => [
                    'Section A: Current Profile Information' => [
                        'Full Registered Name' => '________________________________________________',
                        'Registered Email Address' => '________________________________________________',
                        'Student ID / Unique Identifier' => '________________________________________________',
                        'School / Affiliate Institution' => '________________________________________________',
                    ],
                    'Section B: Requested Changes & Modifications' => [
                        'Attribute to Update (e.g. Name, School)' => '________________________________________________',
                        'Old Value / Information' => '________________________________________________',
                        'New Proposed Value (Corrected)' => '________________________________________________',
                    ],
                    'Section C: Reason for Account Modification' => [
                        'Detailed Explanation/Justification' => "\n\n_________________________________________________________________________________\n\n_________________________________________________________________________________",
                    ]
                ],
                'note' => 'Please attach supporting formal documentation (e.g. Certificate of Enrollment, Registrar Certificate) validating the requested change.'
            ],
            'account_unblock' => [
                'title' => 'ACCOUNT UNBLOCK & PORTAL RESTORATION FORM',
                'form_code' => 'SMRT-FORM-002',
                'sections' => [
                    'Section A: Account Holder Information' => [
                        'Full Registered Name' => '________________________________________________',
                        'Registered Email Address' => '________________________________________________',
                        'Assigned Role (Intern / Supervisor)' => '________________________________________________',
                        'Office / Assignment Division' => '________________________________________________',
                    ],
                    'Section B: Lockout & Security Details' => [
                        'Date / Time of Portal Suspension' => '________________________________________________',
                        'Reason for Suspension (if provided)' => '________________________________________________',
                    ],
                    'Section C: Statement & Security Clearance' => [
                        'Please explain the cause of lockout and state your clearance request' => "\n\n_________________________________________________________________________________\n\n_________________________________________________________________________________\n\n_________________________________________________________________________________",
                    ]
                ],
                'note' => 'Account unblocking is subject to administrative security checks. Ensure all security questions and identity confirmations are validated.'
            ],
            'attendance_adjust' => [
                'title' => 'WEEKLY ATTENDANCE ADJUSTMENT REQUEST',
                'form_code' => 'SMRT-FORM-003',
                'sections' => [
                    'Section A: Intern & Office Details' => [
                        'Intern Full Name' => '________________________________________________',
                        'Assigned Office / Department' => '________________________________________________',
                        'Supervising Office Head' => '________________________________________________',
                    ],
                    'Section B: Attendance Adjustment Entry' => [
                        'Target Log Date (MM/DD/YYYY)' => '________________________________________________',
                        'Type of Adjustment (Clock In / Clock Out)' => '________________________________________________',
                        'Actual Time Rendered (e.g. 08:00 AM)' => '________________________________________________',
                        'Missed Scan Reason (e.g., Geofence, QR Refresh)' => '________________________________________________',
                    ],
                    'Section C: Signatures & Certification' => [
                        'Intern Signature' => '________________________  Date: ________________',
                        'Supervisor Signature' => '________________________  Date: ________________',
                    ]
                ],
                'note' => 'This request must be signed by your direct Supervisor to confirm actual presence in the assigned division.'
            ],
            'mfa_reset' => [
                'title' => 'MFA RESET & SECURITY ADJUSTMENT REQUEST',
                'form_code' => 'SMRT-FORM-004',
                'sections' => [
                    'Section A: Identity Verification' => [
                        'Registered Account Email' => '________________________________________________',
                        'Full Legal Name' => '________________________________________________',
                        'Contact / Mobile Number' => '________________________________________________',
                    ],
                    'Section B: Multi-Factor Authentication Method' => [
                        'MFA Method to Reset' => '[  ] Authenticator App (TOTP)     [  ] Email Code Verification',
                        'Reason for Reset Request' => "\n\n_________________________________________________________________________________\n\n_________________________________________________________________________________",
                    ],
                ],
                'note' => 'For security integrity, MFA resets require physical validation or supervisor endorsement to prevent unauthorized account access.'
            ],
            default => abort(404, 'Template not found.')
        };

        $pdf = Pdf::loadView('templates.pdf', compact('templateData'));

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts'),
            'isHtml5ParserEnabled' => true,
        ]);

        $pdf->setPaper('letter', 'portrait');

        $fileName = strtolower(str_replace(' ', '_', $templateData['title'])).'.pdf';

        return $pdf->download($fileName);
    }
}
