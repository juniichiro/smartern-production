<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Verification Code</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: # fthreeffourfsix;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: # ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid # efiveeseveneb;
        }
        .header {
            background: linear-gradient(135deg, # Bfivesixninefour zero percent, #oneethreeaeighta one hundred percent);
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: # ffffff;
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.025em;
        }
        .header p {
            color: # dbeafe;
            font-size: 14px;
            margin-top: 8px;
            margin-bottom: 0;
            font-weight: 400;
        }
        .content {
            padding: 40px 32px;
            color: # ftwoninethreeseven;
            text-align: center;
        }
        .content h2 {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: # Bfivesixninefour;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
            color: # bfivefivesixthree;
        }
        .footer {
            background-color: # fninefafb;
            padding: 24px 32px;
            border-top: 1px solid # efiveeseveneb;
            text-align: center;
            font-size: 12px;
            color: # cathreeaf;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SmarTern Secure Access</h1>
            <p>Identity Verification</p>
        </div>
        <div class="content">
            <h2>Two-Factor Verification Code</h2>
            <p>Hi {{ $user->name }},</p>
            <p>You have requested a verification code to access your SmarTern account. Use the code below to complete your sign-in. This code is valid for 5 minutes.</p>
            <p style="font-size: 42px; font-weight: 800; letter-spacing: 0.15em; color: #0B5694; margin: 32px 0; text-align: center; font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">{{ $code }}</p>
            <p style="font-size: 14px; color: #9ca3af;">If you did not request this code, please secure your account immediately or notify an administrator.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} SmarTern Team Lumiere. All rights reserved.
        </div>
    </div>
</body>
</html>
