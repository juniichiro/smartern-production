<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Internship Performance & Activity Registry Report</title>
    <style>
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: # ftwoninethreeseven;
            margin: 0; 
            padding: 30px 40px; 
            font-size: 13px; 
            line-height: 1.5;
        }
        
        .header-table { 
            width: 100%; 
            border-bottom: 3px solid # Bfivesixninefour;
            padding-bottom: 20px; 
            margin-bottom: 25px; 
        }
        .logo { 
            width: 60px; 
            vertical-align: middle; 
            margin-right: 15px; 
        }
        .brand { 
            font-size: 28px; 
            font-weight: bold; 
            display: inline-block; 
            vertical-align: middle; 
            color: # Bfivesixninefour;
            letter-spacing: -1px; 
        }
        .official-tag { 
            color: # b;
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 11px; 
            margin-top: 4px; 
        }
        
        .report-meta {
            margin-bottom: 25px;
            font-size: 12px;
            color: # bfivefivesixthree;
        }
        .meta-title {
            font-size: 18px;
            font-weight: bold;
            color: # ;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            table-layout: fixed; 
        }
        .data-table th { 
            background: # fninefafb;
            color: # bfivefivesixthree;
            padding: 12px 10px; 
            text-align: left; 
            font-size: 10px; 
            text-transform: uppercase; 
            border-bottom: 2px solid # efiveeseveneb;
            font-weight: bold;
        }
        .data-table td { 
            padding: 12px 10px; 
            border-bottom: 1px solid # fthreeffourfsix;
            font-size: 12px; 
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-completed { background-color: # dcfceseven; color: #one hundred and sixty-six thousand and five hundred and thirty-four; }
        .badge-pending { background-color: # fthreeffourfsix; color: #fourbfivefivesixthree; }
        
        .intern-name { font-size: 13px; font-weight: bold; color: # ; }
        .intern-email { font-size: 11px; color: # bseventwoeightzero; margin-top: twopx; }
        
        .period-badge {
            margin-top: 5px;
            font-size: 9px;
            font-weight: bold;
            color: # bfivesixninefour;
            text-transform: uppercase;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 10px;
            color: # cathreeaf;
            text-align: center;
            padding-bottom: 15px;
            border-top: 1px solid # efiveeseveneb;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" class="logo">
                @endif
                <div class="brand">SmarTern</div>
                <div class="official-tag">Internship Activity & Performance Report</div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div style="font-size: 10px; color: #6b7280; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Date Generated</div>
                <div style="font-size: 14px; font-weight: bold; color: #111827; margin-top: 2px;">{{ $date }}</div>
            </td>
        </tr>
    </table>

    <div class="report-meta">
        <div class="meta-title">Detailed Performance Registry</div>
        <div><strong>Filter Period:</strong> {{ $periodLabel }}</div>
        @if($search)
            <div><strong>Search Query:</strong> "{{ $search }}"</div>
        @endif
        <div><strong>Total Active Interns:</strong> {{ count($interns) }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Intern Candidate</th>
                <th style="width: 22%;">Assigned Unit</th>
                <th style="width: 25%;">Latest Journal Entry</th>
                <th style="width: 15%; text-align: center;">Accumulated Hours</th>
                <th style="width: 13%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($interns as $intern)
                @php
                    $latestJournal = $intern->journals->first();
                    $isCompleted = optional($intern->certificate)->status === 'published';
                @endphp
                <tr>
                    <td>
                        <div class="intern-name">{{ $intern->first_name }} {{ $intern->last_name }}</div>
                        <div class="intern-email">{{ $intern->user->email ?? 'No email' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #4b5563;">{{ $intern->office->name ?? 'Unassigned' }}</div>
                    </td>
                    <td>
                        @if($latestJournal)
                            <div style="font-weight: bold;">{{ \Carbon\Carbon::parse($latestJournal->entry_date)->format('M d, Y') }}</div>
                            <div style="font-size: 10px; color: #6b7280; margin-top: 1px;">{{ \Carbon\Carbon::parse($latestJournal->created_at)->diffForHumans() }}</div>
                            @if(isset($intern->period_journals_count) && $intern->period_journals_count > 0)
                                <div class="period-badge">{{ $intern->period_journals_count }} {{ Str::plural('log', $intern->period_journals_count) }} in period</div>
                            @endif
                        @else
                            <span style="color: #9ca3af; font-style: italic;">No Activity</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ (int)$intern->hours_rendered }} / {{ $intern->required_hours ?? 480 }} HRS
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-{{ $isCompleted ? 'completed' : 'pending' }}">
                            {{ $isCompleted ? 'completed' : 'pending' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #9ca3af; padding: 30px;">
                        No internship records match the active filter criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by SmarTern Internship Management System &bull; {{ date('Y') }} &bull; Confidential Executive Document
    </div>

</body>
</html>
