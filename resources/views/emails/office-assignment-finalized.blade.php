<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=header-width">
    <title>SmarTern Office Assignment Finalized</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.025em;
        }
        .header p {
            color: #dbeafe;
            font-size: 14px;
            margin-top: 8px;
            margin-bottom: 0;
            font-weight: 400;
        }
        .content {
            padding: 40px 32px;
            color: #1f2937;
        }
        .content h2 {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: #1e3a8a;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
            color: #4b5563;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 15px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            width: 140px;
            font-weight: 600;
            color: #64748b;
        }
        .info-value {
            font-weight: 700;
            color: #0f172a;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #f3f4f6;
            font-size: 13px;
            color: #9ca3af;
        }
        .notice {
            color: #0f5132;
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            padding: 16px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SmarTern</h1>
            <p>Secure Internship Management System</p>
        </div>
        <div class="content">
            <h2>Office Assignment Finalized</h2>
            <p>Hello {{ $applicant->first_name }},</p>
            <p>We are pleased to inform you that your office assignment has been finalized by the Head Supervisor.</p>
            
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Applicant Name:</span>
                    <span class="info-value">{{ $applicant->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Assigned Office:</span>
                    <span class="info-value">{{ $office->name }}</span>
                </div>
            </div>

            <p><strong>What's Next?</strong></p>
            <p>Your user account will be generated shortly by the system administrator. Once approved, you will receive another email containing your system login credentials and starting instructions.</p>

            <div class="notice">
                <strong>Important Note:</strong> If you have any questions regarding your assignment or the next steps of your internship onboarding, please contact your School Coordinator.
            </div>
        </div>
        <div class="footer">
            @php
                $logoPath = public_path('images/blue-smartern.png');
                $logoExists = file_exists($logoPath);
            @endphp
            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto 16px auto; border-collapse: collapse;">
                <tr>
                    <td style="padding-right: 8px; vertical-align: middle;">
                        @if($logoExists)
                            <img src="{{ $message->embedData(file_get_contents($logoPath), 'smartern.png', 'image/png') }}" alt="" style="height: 24px; width: auto; display: block;">
                        @else
                            <img src="{{ asset('images/blue-smartern.png') }}" alt="" style="height: 24px; width: auto; display: block;">
                        @endif
                    </td>
                    <td style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #1e3a8a; vertical-align: middle; line-height: 24px; letter-spacing: -0.01em;">
                        SmarTern
                    </td>
                </tr>
            </table>
            &copy; {{ date('Y') }} SmarTern. All rights reserved. SDO Quezon City.
        </div>
    </div>
</body>
</html>
