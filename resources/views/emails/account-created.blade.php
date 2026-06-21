<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=header-width">
    <title>SmarTern Account Onboarding</title>
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
        .credentials-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .credential-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 15px;
        }
        .credential-row:last-child {
            margin-bottom: 0;
        }
        .credential-label {
            width: 120px;
            font-weight: 600;
            color: #64748b;
        }
        .credential-value {
            font-family: monospace;
            font-weight: 700;
            color: #0f172a;
            word-break: break-all;
        }
        .btn-container {
            text-align: center;
            margin-top: 32px;
        }
        .btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #f3f4f6;
            font-size: 13px;
            color: #9ca3af;
        }
        .warning {
            color: #b45309;
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
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
            <h2>Welcome to SmarTern!</h2>
            <p>Hello,</p>
            <p>An administrator has created a SmarTern account for you. Below are your temporary account credentials. Please use them to log in to the portal.</p>
            
            <div class="credentials-card">
                <div class="credential-row">
                    <span class="credential-label">Portal URL:</span>
                    <span class="credential-value" style="font-family: inherit;"><a href="{{ url('/login') }}" style="color: #3b82f6;">{{ url('/login') }}</a></span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Username:</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">{{ $plainPassword }}</span>
                </div>
            </div>

            <div class="warning">
                <strong>Important Security Notice:</strong> You will be prompted to change your temporary password immediately upon your first login. Do not share these credentials with anyone.
            </div>

            <div class="btn-container">
                <a href="{{ url('/login') }}" class="btn">Log In to Your Account</a>
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
