<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }
        .content-body {
            font-size: 16px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 40px;
        }
        .logo-footer {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-top: 40px;
            margin-bottom: 16px;
        }
        .footer-links {
            font-size: 13px;
            color: #94a3b8;
        }
        .footer-links a {
            color: #0b5694;
            text-decoration: none;
            font-weight: 600;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="content-body">
            {!! $announcement->content !!}
        </div>

        @php
            $logoPath = public_path('images/blue-smartern.png');
            $logoExists = file_exists($logoPath);
        @endphp

        <div class="logo-footer">
            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; border-collapse: collapse;">
                <tr>
                    <td style="padding-right: 8px; vertical-align: middle;">
                        @if($logoExists)
                            <img src="{{ $message->embedData(file_get_contents($logoPath), 'smartern.png', 'image/png') }}" alt="" style="height: 28px; width: auto; display: block;">
                        @else
                            <img src="{{ asset('images/blue-smartern.png') }}" alt="" style="height: 28px; width: auto; display: block;">
                        @endif
                    </td>
                    <td style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 800; color: #1e3a8a; vertical-align: middle; line-height: 28px; letter-spacing: -0.01em;">
                        SmarTern
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer-links">
            <a href="{{ route('intern.announcements.show', $announcement) }}">View announcement</a>
            &nbsp; | &nbsp;
            <a href="{{ route('profile.edit') }}">Update your notification settings</a>
        </div>
    </div>
</body>
</html>
