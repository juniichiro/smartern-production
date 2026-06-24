<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Force Modern Sans-Serif fonts in dompdf */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #1f2937; 
            margin: 0; 
            padding: 35px 45px; 
            font-size: 14px; 
            line-height: 1.5;
        }
        
        /* Header Styling */
        .header-table { width: 100%; border-bottom: 3px solid #0B5694; padding-bottom: 25px; margin-bottom: 30px; }
        .logo { width: 65px; vertical-align: middle; margin-right: 15px; }
        .brand { font-size: 30px; font-weight: bold; display: inline-block; vertical-align: middle; color: #0B5694; letter-spacing: -1px; }
        .official-tag { color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 12px; margin-top: 5px; }

        /* Report Summary Section */
        .report-summary { margin-bottom: 35px; }
        .school-name { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .summary-grid { width: 100%; margin-bottom: 25px; }
        .summary-card { 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            padding: 18px; 
            background: #f9fafb;
            vertical-align: top;
            width: 32%;
        }
        .summary-label { 
            font-size: 11px; 
            font-weight: bold; 
            color: #6b7280; 
            text-transform: uppercase; 
            margin-bottom: 8px; 
            letter-spacing: 0.5px;
        }
        .summary-value { font-size: 20px; font-weight: bold; color: #111827; }

        /* Table Styling */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .data-table th { 
            background: #f9fafb; 
            color: #4b5563; 
            padding: 15px 12px; 
            text-align: left; 
            font-size: 11px; 
            text-transform: uppercase; 
            border-bottom: 2px solid #e5e7eb;
            font-weight: bold;
        }
        .data-table td { 
            padding: 15px 12px; 
            border-bottom: 1px solid #f3f4f6; 
            font-size: 13px; 
            vertical-align: middle;
            word-wrap: break-word;
        }
        
        /* Status Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-active { background-color: #dbeafe; color: #1e40af; }
        .badge-endorsing { background-color: #fef9c3; color: #854d0e; }
        .badge-completed { background-color: #dcfce7; color: #166534; }
        .badge-risk { background-color: #fee2e2; color: #991b1b; }

        .intern-id { color: #0B5694; font-weight: bold; font-size: 11px; }
        .intern-name { font-size: 14px; font-weight: bold; color: #111827; margin-top: 2px; }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
            padding-bottom: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                @if($logo)
                    <img src="{{ $logo }}" class="logo">
                @endif
                <div class="brand">SmarTern</div>
                <div class="official-tag">Official Student Registry</div>
            </td>
            <td style="text-align: right;">
                <div style="font-size: 11px; color: #6b7280; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">Date Generated</div>
                <div style="font-size: 16px; font-weight: bold; color: #111827; margin-top: 3px;">{{ $date }}</div>
            </td>
        </tr>
    </table>

    <div class="report-summary">
        <div class="school-name">Institution: {{ $school }}</div>
        
        <table class="summary-grid">
            <tr>
                <td class="summary-card">
                    <div class="summary-label">Student Count</div>
                    <div class="summary-value">{{ count($interns) }} Enrolled</div>
                </td>
                <td style="width: 2%;"></td>
                <td class="summary-card">
                    <div class="summary-label">Offices Assigned</div>
                    <div class="summary-value">{{ $departmentCount }} Departments</div>
                </td>
                <td style="width: 2%;"></td>
                <td class="summary-card">
                    <div class="summary-label">Report Status</div>
                    <div class="summary-value">Authenticated</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28%;">Student Details</th>
                <th style="width: 26%;">Assigned Office</th>
                <th style="width: 22%;">Training Status</th>
                <th style="width: 12%; text-align: center;">Hours</th>
                <th style="width: 12%; text-align: center;">Absences</th>
            </tr>
        </thead>
        <tbody>
            @foreach($interns as $intern)
            @php
                $status = 'active';
                $statusLabel = 'In-Training';
                if ($intern->certificate && $intern->certificate->status === 'published') {
                    $status = 'completed';
                    $statusLabel = 'Certified';
                } elseif ($intern->hours_rendered >= ($intern->required_hours ?? 480)) {
                    $status = 'endorsing';
                    $statusLabel = 'Endorsement Due';
                }
                
                $isAtRisk = $intern->absent_count > 2;
            @endphp
            <tr>
                <td>
                    <div class="intern-id">{{ $intern->user->display_id }}</div>
                    <div class="intern-name">{{ $intern->last_name }}, {{ $intern->first_name }}</div>
                </td>
                <td>
                    <div style="font-weight: 500;">{{ $intern->office->name ?? 'Not Assigned' }}</div>
                </td>
                <td>
                    <span class="badge badge-{{ $status }}">{{ $statusLabel }}</span>
                    @if($isAtRisk)
                        <div style="margin-top: 6px;"><span class="badge badge-risk">At Risk</span></div>
                    @endif
                </td>
                <td style="text-align: center;">
                    <div style="font-weight: bold; font-size: 14px;">{{ (int)$intern->hours_rendered }}</div>
                </td>
                <td style="text-align: center;">
                    <div style="font-weight: bold; font-size: 14px; color: {{ $isAtRisk ? '#b91c1c' : '#111827' }};">
                        {{ $intern->absent_count }}
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated by SmarTern Internship Management System &bull; {{ date('Y') }} &bull; Academic Oversight Document
    </div>

</body>
</html>