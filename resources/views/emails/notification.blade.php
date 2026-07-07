<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->type }}</title>
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
            margin-top: 32px;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
        }
        .footer-links a {
            color: #0b5694;
            text-decoration: none;
            font-weight: 600;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        .remarks-box {
            margin: 20px auto;
            max-width: 500px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 16px;
            text-align: left;
            font-size: 15px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="content-body">
            @if($notification->category === 'task' && isset($extraData['task']))
                @php
                    $task = $extraData['task'];
                    $typeLower = strtolower($notification->type);
                @endphp

                @if($typeLower === 'task approved')
                    Your assignment <strong>{{ $task->title }}</strong> has been approved.
                    <br><br>
                    <span style="font-size: 14px; color: #64748b; font-style: italic;">
                        approved: {{ now()->format('M d \a\t g:ia') }}
                    </span>
                    @if(!empty($task->remarks))
                        <br><br>
                        <span style="font-size: 14px; color: #64748b; font-style: italic;">
                            remarks: {{ $task->remarks }}
                        </span>
                    @endif
                @elseif($typeLower === 'revision requested')
                    Your assignment <strong>{{ $task->title }}</strong> has been revised.
                    @if(!empty($extraData['due_date_modified']) && $task->due_date)
                        <br><br>
                        <span style="font-size: 14px; color: #b91c1c; font-style: italic; font-weight: 600;">
                            new due date: {{ $task->due_date->format('M d \a\t g:ia') }}
                        </span>
                    @endif
                    <br><br>
                    <span style="font-size: 14px; color: #64748b; font-style: italic;">
                        revised: {{ now()->format('M d \a\t g:ia') }}
                    </span>
                    @if(!empty($task->remarks))
                        <br><br>
                        <span style="font-size: 14px; color: #64748b; font-style: italic;">
                            remarks: {{ $task->remarks }}
                        </span>
                    @endif
                @elseif($typeLower === 'task assignment')
                    You have been assigned a new task: <strong>{{ $task->title }}</strong>.
                    @if($task->due_date)
                        <br><br>
                        <span style="font-size: 14px; color: #64748b; font-style: italic;">
                            due: {{ $task->due_date->format('M d \a\t g:ia') }}
                        </span>
                    @endif
                @else
                    {{ $notification->message }}
                @endif
            @else
                {{ $notification->message }}
            @endif
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
                            <img src="{{ env('AWS_URL') ? env('AWS_URL') . '/images/blue-smartern.png' : asset('images/blue-smartern.png') }}" alt="" style="height: 28px; width: auto; display: block;">
                        @endif
                    </td>
                    <td style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 800; color: #1e3a8a; vertical-align: middle; line-height: 28px; letter-spacing: -0.01em;">
                        SmarTern
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer-links">
            @if($notification->target_url)
                <a href="{{ $notification->target_url }}">View assignment</a>
            @else
                <a href="{{ route('intern.dashboard') }}">View portal</a>
            @endif
            &nbsp; | &nbsp;
            <a href="{{ route('profile.edit') }}">Update your notification settings</a>
        </div>
    </div>
</body>
</html>
