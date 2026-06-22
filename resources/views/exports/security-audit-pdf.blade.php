<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SmarTern Security Audit Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            line-height: 1.5;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #0B5694;
            padding-bottom: 20px;
        }
        .logo {
            width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #0B5694;
            font-size: 24px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-info {
            margin-bottom: 30px;
            font-size: 12px;
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        th {
            background-color: #f9fafb;
            color: #4b5563;
            text-transform: uppercase;
            font-weight: bold;
            padding: 12px 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .level {
            padding: 2px 8px;
            border-radius: 9999px;
            color: white;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
            font-size: 8px;
        }
        .level-critical { background-color: #ef4444; }
        .level-warning { background-color: #f59e0b; }
        .level-normal { background-color: #10b981; }
        
        .score-bar-bg {
            width: 60px;
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }
        .score-bar-fill {
            height: 100%;
            border-radius: 3px;
        }
        
        .footer {
            margin-top: 50px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Security Audit Report</h1>
        <p style="margin: 5px 0 0; font-weight: bold; color: #4b5563;">SmarTern: Internship Management System</p>
    </div>

    <div class="report-info">
        <table style="width: auto; margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none; padding: 2px 20px 2px 0; color: #4b5563;"><b>Generated Date:</b></td>
                <td style="border: none; padding: 2px 0;">{{ $date }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px 20px 2px 0; color: #4b5563;"><b>Total Logs Analyzed:</b></td>
                <td style="border: none; padding: 2px 0;">{{ $logs->count() }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Timestamp</th>
                <th style="width: 13%;">Activity</th>
                <th style="width: 15%;">User</th>
                <th style="width: 25%;">Details</th>
                <th style="width: 15%;">Device</th>
                <th style="width: 13%;">Anomaly Score</th>
                <th style="width: 7%;">Level</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            @php
                $score = (float)$log->anomaly_score;
                $levelClass = $score > 0.7 ? 'level-critical' : ($score > 0.5 ? 'level-warning' : 'level-normal');
                $levelText = $score > 0.7 ? 'Critical' : ($score > 0.5 ? 'Warning' : 'Normal');
                $scoreColor = $score > 0.7 ? '#ef4444' : ($score > 0.5 ? '#f59e0b' : '#10b981');
                
                $userName = 'System';
                if ($log->internDetails) {
                    $userName = $log->internDetails->first_name . ' ' . $log->internDetails->last_name . ' (Intern)';
                } elseif ($log->supervisorDetails) {
                    $userName = $log->supervisorDetails->first_name . ' ' . $log->supervisorDetails->last_name . ' (Supervisor)';
                } elseif ($log->user) {
                    $userName = ($log->user->role_id === \App\Enums\UserRole::ADMIN) ? 'SmarTern Admin' : $log->user->email;
                }
            @endphp
            <tr>
                <td>{{ $log->timestamp->format('Y-m-d H:i') }}</td>
                <td><b>{{ $log->action }}</b></td>
                <td>{{ $userName }}</td>
                <td>
                    {{ $log->resource }}<br>
                    <span style="color: #9ca3af; font-size: 8px;">IP: {{ $log->ip_address ?? 'Unknown' }}</span>
                </td>
                <td style="font-size: 9px; color: #4b5563;">
                    {{ str_replace('|', ' • ', $log->device ?? 'System') }}
                </td>
                <td>
                    <div class="score-bar-bg">
                        <div class="score-bar-fill" style="width: {{ $score * 100 }}%; background-color: {{ $scoreColor }};"></div>
                    </div>
                    <b>{{ number_format($score, 2) }}</b>
                </td>
                <td>
                    <span class="level {{ $levelClass }}">{{ $levelText }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential SmarTern Security Report &copy; {{ date('Y') }} — This is a system-generated document.
    </div>

</body>
</html>