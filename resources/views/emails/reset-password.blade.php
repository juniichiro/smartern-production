<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
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
            background: linear-gradient(135deg, #0B5694 0%, #1e3a8a 100%);
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
            text-align: center;
        }
        .content h2 {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: #0B5694;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
            color: #4b5563;
            text-align: left;
        }
        .content .btn-container {
            margin: 32px 0;
            text-align: center;
        }
        .content .btn {
            display: inline-block;
            background-color: #0B5694;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            font-weight: 700;
            font-size: 14px;
            border-radius: 12px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            box-shadow: 0 4px 6px -1px rgba(11, 86, 148, 0.2), 0 2px 4px -1px rgba(11, 86, 148, 0.1);
            transition: all 0.2s ease-in-out;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        .content .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 24px 0;
        }
        .content .small-text {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.5;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SmarTern Secure Access</h1>
            <p>Password Recovery</p>
        </div>
        <div class="content">
            <h2>Reset Your Password</h2>
            <p>Hi {{ $user->name }},</p>
            <p>You are receiving this email because we received a password reset request for your SmarTern account. Click the button below to set a new password. This reset link will expire in 60 minutes.</p>
            
            <div class="btn-container">
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Reset Password</a>
            </div>

            <p style="text-align: left;">If you did not request a password reset, no further action is required and your account remains secure.</p>
            
            <div class="divider"></div>
            
            <p class="small-text">
                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $resetUrl }}" style="color: #0B5694; text-decoration: underline; word-break: break-all;">{{ $resetUrl }}</a>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} SmarTern Team Lumiere. All rights reserved.
        </div>
    </div>
</body>
</html>
