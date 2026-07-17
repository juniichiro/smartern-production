<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmarTern Onboarding Application Status Update</title>
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
            background: linear-gradient(135deg, #be123c 0%, #f43f5e 100%);
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.025em;
        }
        .header p {
            color: #ffe4e6;
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
            color: #be123c;
        }
        .content p {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
            color: #4b5563;
        }
        .reason-box {
            background-color: #fff1f2;
            border-left: 4px solid #f43f5e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .reason-title {
            font-[11px] font-black uppercase tracking-wider text-[#9f1239] mb-1;
        }
        .reason-text {
            font-size: 14px;
            color: #be123c;
            margin: 0;
            font-weight: 500;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            border-t: 1px solid #f3f4f6;
        }
        .footer p {
            font-size: 12px;
            color: #9ca3af;
            margin: 0;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Status Update</h1>
            <p>SmarTern Intern Onboarding Portal</p>
        </div>
        <div class="content">
            <h2>Hello {{ $applicantName }},</h2>
            <p>Thank you for your interest in the SmarTern Internship Program. We appreciate the time and effort you put into submitting your application.</p>
            <p>After careful review of your application materials and qualifications, we regret to inform you that we are unable to proceed with your onboarding at this time.</p>
            
            @if($reason)
                <div class="reason-box">
                    <p style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #9f1239; margin: 0 0 4px 0;">Reason for Update</p>
                    <p class="reason-text">{{ $reason }}</p>
                </div>
            @endif

            <p>Please note that according to our onboarding registry policies, you may submit a new application after 2 months. We wish you all the best in your future academic and professional endeavors.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SmarTern. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
