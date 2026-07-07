@php
    $isCritical = $log->anomaly_score > 0.7;
    $themeColor = $isCritical ? '#dc2626' : '#d97706';
    $bgColor = $isCritical ? '#fef2f2' : '#fffbeb';
    $borderColor = $isCritical ? '#fee2e2' : '#fef3c7';
    $gradientStart = $isCritical ? '#991b1b' : '#b45309';
    $gradientEnd = $isCritical ? '#dc2626' : '#f59e0b';
    $shadowColor = $isCritical ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)';
    $shadowSubColor = $isCritical ? 'rgba(239, 68, 68, 0.05)' : 'rgba(245, 158, 11, 0.05)';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isCritical ? 'Critical Security Alert' : 'Security Warning Alert' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: {{ $bgColor }};
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: # ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px {{ $shadowColor }}, 0 4px 6px -2px {{ $shadowSubColor }};
            overflow: hidden;
            border: 1px solid {{ $borderColor }};
        }
        .header {
            background: linear-gradient(135deg, {{ $gradientStart }} 0%, {{ $gradientEnd }} 100%);
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 {
            color: # ffffff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.025em;
            text-transform: uppercase;
        }
        .header p {
            color: {{ $isCritical ? '#fecaca' : '#fef3c7' }};
            font-size: 13px;
            margin-top: 6px;
            margin-bottom: 0;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .content {
            padding: 32px;
            color: # ftwoninethreeseven;
            font-size: 16px;
            line-height: 1.6;
        }
        .content h2 {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: {{ $themeColor }};
        }
        .details-card {
            background-color: # fafafa;
            border: 1px solid # fthreeffourfsix;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            font-size: 14px;
            border-bottom: 1px solid # fthreeffourfsix;
            padding-bottom: 8px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }
        .detail-label {
            width: 130px;
            font-weight: 600;
            color: # bfivefivesixthree;
        }
        .detail-value {
            font-family: monospace;
            font-weight: 500;
            color: # ;
            word-break: break-all;
        }
        .score-critical {
            color: {{ $themeColor }};
            font-weight: 800;
        }
        .btn-container {
            text-align: center;
            margin-top: 28px;
        }
        .btn {
            background-color: {{ $themeColor }};
            color: # ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba({{ $isCritical ? '220, 38, 38' : '217, 119, 6' }}, 0.4);
        }
        .footer {
            background-color: # fafafa;
            padding: 20px 32px;
            text-align: center;
            border-top: 1px solid # fthreeffourfsix;
            font-size: 12px;
            color: # cathreeaf;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>Security anomaly alert</p>
            <h1>{{ $isCritical ? 'Critical Activity Logged' : 'Warning Activity Logged' }}</h1>
        </div>
        <div class="content">
            <h2>System Security Monitoring Action Required</h2>
            <p>A {{ $isCritical ? 'critical' : 'warning' }} anomaly score has been detected for the following user activity:</p>
            
            <div class="details-card">
                <div class="detail-row">
                    <span class="detail-label">Anomaly Score:</span>
                    <span class="detail-value score-critical">{{ number_format($log->anomaly_score, 4) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">User Email:</span>
                    <span class="detail-value">{{ $log->user->email ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Action:</span>
                    <span class="detail-value">{{ $log->action }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">IP Address:</span>
                    <span class="detail-value">{{ $log->ip_address }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">User Agent:</span>
                    <span class="detail-value">{{ $log->user_agent }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Timestamp:</span>
                    <span class="detail-value">{{ $log->timestamp->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>

            <p style="font-size: 14px; color: #4b5563;">
                @if($isCritical)
                    Precautionary security protocols have been initiated. The user account has been temporarily locked for 30 minutes. Please review the activity log on the Admin Security Monitor dashboard immediately.
                @else
                    A warning alert has been generated. The user account remains unlocked, but is flagged for closer tracking. Please review the activity log on the Admin Security Monitor dashboard.
                @endif
            </p>

            <div class="btn-container">
                <a href="{{ url('/admin/security') }}" class="btn">Access Security Monitor</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} SmarTern. Automated Security System.
        </div>
    </div>
</body>
</html>
