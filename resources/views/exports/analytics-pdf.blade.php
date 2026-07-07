<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SmarTern Analytics Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: # ftwoninethreeseven;
            margin: 0;
            padding: 30px 40px;
            /* Increased global base size for better readability*/
            font-size: 13px; 
            line-height: 1.6;
            background-color: # ffffff;
        }

        .header {
            border-bottom: 3.5px solid # Bfivesixninefour;
            padding-bottom: 20px;
            margin-bottom: 35px;
        }

        .header table {
            width: 100%;
        }

        .logo {
            width: 55px;
            height: auto;
            vertical-align: middle;
            margin-right: 12px;
        }

        .brand {
            font-size: 30px;
            font-weight: bold;
            color: # E;
            margin: 0;
            display: inline-block;
            vertical-align: middle;
        }

        .subtitle {
            font-size: 13px;
            font-weight: bold;
            color: # Bfivesixninefour;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 5px;
        }

        .date-info {
            text-align: right;
            font-size: 12px;
            color: # E;
            text-transform: uppercase;
        }

        /* Stats Cards*/
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }

        .stat-card-content {
            background-color: # fninefafb;
            border: 1.5px solid # fthreeffourfsix;
            padding: 25px;
            border-radius: 15px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: bold;
            color: # E;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 32px; /* Large, bold values*/
            font-weight: bold;
            color: # E;
        }

        /* Visual Analytics Section*/
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: # Bfivesixninefour;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* Data Table Readability*/
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th {
            background-color: # E;
            color: # ffffff;
            font-size: 13px;
            padding: 15px;
            text-align: left;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid # fthreeffourfsix;
            font-size: 13px; /* Stronger table text*/
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: # donedfivedb;
            padding-bottom: 25px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    @if(isset($logoSrc))
                        <img src="{{ $logoSrc }}" class="logo">
                    @endif
                    <div class="brand">SmarTern</div>
                    <div class="subtitle">Official Analytics Report</div>
                </td>
                <td class="date-info">
                    Report Generation Date<br>
                    <span style="color: #04091E; font-size: 15px; font-weight: bold;">{{ $date }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="stats-table">
        <tr>
            <td style="width: 48%;">
                <div class="stat-card-content">
                    <div class="stat-label">Total Program Applicants</div>
                    <div class="stat-value">{{ number_format($totalApplicants) }}</div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%;">
                <div class="stat-card-content">
                    <div class="stat-label">Currently Deployed Interns</div>
                    <div class="stat-value">{{ number_format($activeInterns) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Visual Trends</div>
    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            <td style="width: 48%; text-align: center;">
                <img src="{{ $completionChart }}" style="width: 100%; border: 1px solid #f3f4f6; border-radius: 12px;">
                <div style="font-size: 11px; color: #9ca3af; margin-top: 8px; font-weight: bold;">Completion Trends</div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; text-align: center;">
                <img src="{{ $schoolChart }}" style="width: 100%; border: 1px solid #f3f4f6; border-radius: 12px;">
                <div style="font-size: 11px; color: #9ca3af; margin-top: 8px; font-weight: bold;">Partner Distribution</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Department Insights</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Department / Office</th>
                <th style="text-align: right;">Intern Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $dept)
            <tr>
                <td style="font-weight: bold; color: #0B5694; font-size: 14px;">{{ $dept->name }}</td>
                <td style="text-align: right; font-weight: bold; font-size: 14px;">{{ $dept->intern_details_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential SmarTern Report &copy; {{ date('Y') }} — This is a system-generated document.
    </div>

</body>
</html>