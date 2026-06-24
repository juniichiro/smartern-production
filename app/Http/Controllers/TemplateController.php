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
            ]
        ];

        return view('templates.index', compact('templates'));
    }

    /**
     * Download the printable request form PDF.
     */
    public function download($type)
    {
        switch ($type) {
            case 'account_edit':
                $user = auth()->user();
                $role = $user?->role_id;

                $profileFields = [
                    'Full Registered Name' => '________________________________________________',
                    'Registered Email Address' => '________________________________________________',
                ];

                if ($role === \App\Enums\UserRole::INTERN) {
                    $profileFields['Student ID / Intern ID Number'] = '________________________________________________';
                    $profileFields['Affiliated School / University'] = '________________________________________________';
                    $profileFields['Course / Program'] = '________________________________________________';
                } elseif ($role === \App\Enums\UserRole::SUPERVISOR || $role === \App\Enums\UserRole::HEAD_SUPERVISOR) {
                    $profileFields['Supervisor ID / Employee Number'] = '________________________________________________';
                    $profileFields['Assigned Office / Department'] = '________________________________________________';
                } elseif ($role === \App\Enums\UserRole::SCHOOL_COORDINATOR) {
                    $profileFields['Coordinator ID / Employee Number'] = '________________________________________________';
                    $profileFields['Affiliated School / Institution'] = '________________________________________________';
                } else {
                    $profileFields['Admin ID / Employee Number'] = '________________________________________________';
                }

                $templateData = [
                    'title' => 'ACCOUNT DETAILS MODIFICATION REQUEST',
                    'form_code' => 'SMRT-FORM-001',
                    'sections' => [
                        'Section A: Current Profile Information' => [
                            'type' => 'fields',
                            'data' => $profileFields
                        ],
                        'Section B: Requested Changes & Modifications' => [
                            'type' => 'fields',
                            'data' => [
                                'Attribute to Update (e.g. Name, School)' => '________________________________________________',
                                'Old Value / Information' => '________________________________________________',
                                'New Proposed Value (Corrected)' => '________________________________________________',
                            ]
                        ],
                        'Section C: Reason for Account Modification' => [
                            'type' => 'fields',
                            'data' => [
                                'Detailed Explanation/Justification' => "\n_________________________________________________________________________________\n_________________________________________________________________________________",
                            ]
                        ]
                    ],
                    'note' => 'Please attach supporting formal documentation (e.g. Certificate of Enrollment, Registrar Certificate, or Official Assignment Order) validating the requested change.'
                ];
                break;
            case 'account_unblock':
                $templateData = [
                    'title' => 'ACCOUNT UNBLOCK & PORTAL RESTORATION FORM',
                    'form_code' => 'SMRT-FORM-002',
                    'sections' => [
                        'Section A: Account Holder Information' => [
                            'type' => 'fields',
                            'data' => [
                                'Full Registered Name' => '________________________________________________',
                                'Registered Email Address' => '________________________________________________',
                                'Assigned Role (Intern / Supervisor)' => '________________________________________________',
                                'Office / Assignment Division' => '________________________________________________',
                            ]
                        ],
                        'Section B: Lockout & Security Details' => [
                            'type' => 'fields',
                            'data' => [
                                'Date / Time of Portal Suspension' => '________________________________________________',
                                'Reason for Suspension (if provided)' => '________________________________________________',
                            ]
                        ],
                        'Section C: Statement & Security Clearance' => [
                            'type' => 'fields',
                            'data' => [
                                'Please explain the cause of lockout and state your clearance request' => "\n_________________________________________________________________________________\n_________________________________________________________________________________",
                            ]
                        ]
                    ],
                    'note' => 'Account unblocking is subject to administrative security checks. Ensure all security questions and identity confirmations are validated.'
                ];
                break;
            default:
                abort(404, 'Template not found.');
        }

        $fontPath = storage_path('fonts');
        if (!file_exists($fontPath)) {
            mkdir($fontPath, 0775, true);
        }

        $pdf = Pdf::loadView('templates.pdf', compact('templateData'));

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
            'fontDir' => $fontPath,
            'fontCache' => $fontPath,
            'isHtml5ParserEnabled' => true,
        ]);

        $pdf->setPaper('letter', 'portrait');

        $fileName = strtolower(str_replace(' ', '_', $templateData['title'])).'.pdf';

        return $pdf->download($fileName);
    }
}
