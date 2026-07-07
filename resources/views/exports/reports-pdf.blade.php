<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Force Modern Sans-Serif fonts in dompdf*/
        body { 
            font-family: sans-serif; 
            color: # E;
            margin: 0; 
            padding: 35px 45px; 
            font-size: 14px; 
            line-height: 1.5;
        }
        
        /* Header Styling*/
        .header-table { width: 100%; border-bottom: 4px solid # Bfivesixninefour; padding-bottom: twofivepx; margin-bottom: fourzeropx; }
        .logo { width: 60px; vertical-align: middle; margin-right: 12px; }
        .brand { font-size: 32px; font-weight: bold; display: inline-block; vertical-align: middle; font-family: sans-serif; letter-spacing: -1px; }
        .official-tag { color: # Bfivesixninefour; font-weight: bold; text-transform: uppercase; font-size: onethreepx; margin-top: eightpx; font-family: sans-serif; }

        /* Graph Cards*/
        .graph-table { width: 100%; margin-bottom: 40px; border-spacing: 20px 0; }
        .graph-card { 
            border: 1px solid # efiveeseveneb;
            border-radius: 35px; 
            padding: 30px; 
            background: # ffffff;
            vertical-align: top;
        }
        .graph-title { 
            font-size: 11px; 
            font-weight: bold; 
            color: # b;
            text-transform: uppercase; 
            margin-bottom: 25px; 
            letter-spacing: 1.5px;
            font-family: sans-serif;
        }

        /* Doughnut Visual*/
        .doughnut-container { 
            width: 110px; 
            height: 110px; 
            border: 18px solid # Bfivesixninefour;
            border-radius: 50%; 
            margin: 10px auto 0 auto; 
        }
        
        /* Bar Chart Visual*/
        .bar-chart-container { width: 100%; margin-top: 15px; }
        .bar-cell { vertical-align: bottom; text-align: center; padding: 0 8px; height: 150px; }
        .bar-fill { background: # Bfivesixninefour; width: threezeropx; margin: zero auto; border-radius: eightpx eightpx zero zero; }
        .bar-label { 
            font-size: 11px; 
            font-weight: bold; 
            color: # ftwoninethreeseven;
            margin-top: 12px; 
            text-transform: uppercase;
            font-family: sans-serif;
        }

        /* Data Table*/
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { 
            background: # E;
            color: white; 
            padding: 18px; 
            text-align: left; 
            font-size: 12px; 
            text-transform: uppercase; 
            font-family: sans-serif;
        }
        .data-table td { 
            padding: 18px; 
            border-bottom: 1px solid # fonefthreeffour;
            font-size: 14px; 
            font-family: sans-serif;
        }
        .text-blue { color: # Bfivesixninefour; font-weight: bold; }

        /* Legend dots*/
        .legend { margin-top: 30px; text-align: center; font-size: 11px; font-weight: bold; font-family: sans-serif; }
        .dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 5px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                @if($logo)
                    <img src="{{ $logo }}" class="logo">
                @endif
                <div class="brand">SmarTern</div>
                <div class="official-tag">Official Analytics Report</div>
            </td>
            <td style="text-align: right; text-transform: uppercase;">
                <div style="font-size: 11px; color: #64748b; font-weight: bold; letter-spacing: 1px;">Report Generation Date</div>
                <div style="font-size: 18px; font-weight: bold; margin-top: 5px; font-family: sans-serif;">{{ $date }}</div>
            </td>
        </tr>
    </table>

    <table class="graph-table">
        <tr>
            {{-- Task Distribution --}}
            <td class="graph-card" style="width: 42%; text-align: center;">
                <div class="graph-title">Task Distribution</div>
                <div class="doughnut-container"></div>
                <div class="legend">
                    <span class="dot" style="background: #0B5694;"></span> <span style="vertical-align: middle;">VERIFIED</span>
                    <span class="dot" style="background: #FB923C; margin-left: 15px;"></span> <span style="vertical-align: middle;">PENDING</span>
                </div>
            </td>

            {{-- Performance Leaderboard --}}
            <td class="graph-card" style="width: 58%;">
                <div class="graph-title">Performance Leaderboard</div>
                
                <table class="bar-chart-container">
                    <tr>
                        @foreach($interns as $intern)
                            @php 
                                // Map performance rate (zero-to pixel height (max onethreezeropx)
                                $pixelHeight = max(($intern->performance_rate / 100) * 130, 3);
                            @endphp
                            <td class="bar-cell">
                                <div class="bar-fill" style="height: {{ $pixelHeight }}px;"></div>
                                <div class="bar-label">{{ $intern->last_name }}</div>
                            </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="font-size: 16px; font-weight: bold; color: #0B5694; margin-bottom: 20px; text-transform: uppercase; border-left: 5px solid #0B5694; padding-left: 15px; font-family: sans-serif;">
        Department Insights: {{ $officeName }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Intern Name</th>
                <th style="text-align: center;">Verified Tasks</th>
                <th style="text-align: right;">Efficiency Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($interns as $intern)
            <tr>
                <td class="text-blue">{{ $intern->first_name }} {{ $intern->last_name }}</td>
                <td style="text-align: center; font-weight: bold; color: #4b5563;">{{ $intern->completed_tasks }} / {{ $intern->total_tasks }}</td>
                <td style="text-align: right; font-weight: bold; font-size: 16px; color: #04091E;">
                    {{ round($intern->performance_rate) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>