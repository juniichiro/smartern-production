<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #0B5694; padding-bottom: 20px; }
        .title { text-transform: uppercase; font-weight: bold; font-size: 22px; color: #0B5694; margin-bottom: 5px; }
        .subtitle { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 2px; }
        
        .intern-info { margin-top: 30px; margin-bottom: 30px; width: 100%; }
        .intern-info td { vertical-align: top; font-size: 13px; padding-bottom: 5px; }
        
        table.tasks-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            table-layout: fixed; /* Crucial: keeps columns at set widths */
        }
        .tasks-table th { 
            background-color: #0B5694; 
            color: white; 
            text-align: left; 
            padding: 10px; 
            font-size: 11px; 
            text-transform: uppercase; 
        }
        .tasks-table td { 
            padding: 10px; 
            border-bottom: 1px solid #eee; 
            font-size: 11px; 
            vertical-align: top;
            /* Force long filenames to wrap */
            word-wrap: break-word; 
            overflow-wrap: break-word;
            word-break: break-all; 
        }
        
        .file-name { 
            display: block;
            color: #4b5563; 
            font-family: 'Courier', monospace; /* Monospace helps distinguish file names */
            font-size: 10px;
            background: #f9fafb;
            padding: 4px;
            border: 1px solid #e5e7eb;
        }
        
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 10px; color: #aaa; text-align: center; padding-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Accomplishment Report</div>
        <div class="subtitle">SmarTern Internship Management System</div>
    </div>

    <table class="intern-info">
        <tr>
            <td width="50%">
                <strong>Name:</strong> {{ $intern->first_name }} {{ $intern->last_name }}<br>
                <strong>Student ID:</strong> {{ $intern->id }}<br>
                <strong>Office:</strong> {{ $intern->office->name }}
            </td>
            <td width="50%" align="right">
                <strong>Total Hours:</strong> {{ $total_hours }} / {{ $intern->required_hours ?? $intern->office->total_hours_required ?? 480 }} hrs<br>
                <strong>Date Generated:</strong> {{ $date }}
            </td>
        </tr>
    </table>

    <table class="tasks-table">
        <thead>
            <tr>
                <th width="25%">Task Title</th>
                <th width="15%">Verified</th>
                <th width="35%">Intern's Notes</th>
                <th width="25%">Attached File</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                @php 
                    $latestSubmission = $task->submissions->first(); 
                @endphp
                <tr>
                    <td><strong>{{ $task->title }}</strong></td>
                    <td>{{ $task->updated_at->format('M d, Y') }}</td>
                    <td style="color: #666;">
                        {{ $latestSubmission->content ?? 'N/A' }}
                    </td>
                    <td>
                        @if($latestSubmission && $latestSubmission->file_path)
                            <div class="file-name">
                                {{ basename($latestSubmission->file_path) }}
                            </div>
                        @else
                            <span style="color: #ccc; font-style: italic;">No file</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" align="center" style="padding: 40px; color: #999;">No verified accomplishments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        This document serves as an official record of tasks completed within the SmarTern Portal.
    </div>
</body>
</html>