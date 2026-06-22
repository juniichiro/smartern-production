<?php

use App\Models\Attendance;
use App\Models\InternDetail;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Runs the ML script every minute in the background
Schedule::command('app:recalculate-anomaly-scores')->everyMinute()->withoutOverlapping();

// Automated Daily System Backup at 12:40 PM
Schedule::command('backup:run --type=auto')->dailyAt('12:40');

// Automated Daily Audit Log Archival and Pruning (retaining 90 days)
Schedule::command('app:archive-audit-logs --days=90')->daily()->withoutOverlapping();

// Automated Daily Absence Logging at 6:00 PM on Weekdays
Artisan::command('app:mark-absent', function () {
    $activeInterns = InternDetail::whereDoesntHave('certificate', function ($query) {
        $query->where('status', 'published');
    })->get();

    $today = now()->toDateString();
    $count = 0;

    foreach ($activeInterns as $intern) {
        $exists = Attendance::where('intern_id', $intern->user_id)
            ->whereDate('clock_in', $today)
            ->exists();

        if (! $exists) {
            Attendance::create([
                'intern_id' => $intern->user_id,
                'office_id' => $intern->office_id ?? 1,
                'clock_in' => Carbon::today()->setTime(8, 0, 0),
                'clock_out' => Carbon::today()->setTime(17, 0, 0),
                'status' => 'absent',
                'remarks' => 'Absent for the day',
            ]);
            $count++;
        }
    }

    $this->info("Successfully logged {$count} absent records for today.");
})->purpose('Mark active interns who did not clock in today as absent');

Schedule::command('app:mark-absent')->dailyAt('18:00')->weekdays();
