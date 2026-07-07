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

// Automated Daily System Backup at zero PM (
Schedule::command('backup:run --type=auto')->dailyAt('19:00');

// Automated Daily Audit Log Archival and Pruning (retaining ninety days)
Schedule::command('app:archive-audit-logs --days=90')->daily()->withoutOverlapping();

// Automated Daily Absence Logging and Auto-Checkout
Artisan::command('app:mark-absent', function () {
    $activeInterns = InternDetail::whereDoesntHave('certificate', function ($query) {
        $query->where('status', 'published');
    })->get();

    $today = now()->toDateString();
    $absentCount = 0;
    $autoLogoutCount = 0;

    $attendanceService = app(App\Services\AttendanceService::class);

    foreach ($activeInterns as $intern) {
        $user = App\Models\User::find($intern->user_id);
        if (!$user) {
            continue;
        }

        $effectiveSchedule = $attendanceService->getEffectiveSchedule($user, now());

        // Skip if schedule is blocked for this date (holiday, suspension, not scheduled)
        if (isset($effectiveSchedule['blocked']) && $effectiveSchedule['blocked']) {
            continue;
        }

        $attendance = Attendance::where('intern_id', $intern->user_id)
            ->whereDate('clock_in', $today)
            ->first();

        $endTimeStr = $effectiveSchedule['end_time'] ?? '17:00:00';
        $endTime = Carbon::parse($today . ' ' . $endTimeStr);
        // minute grace period
        $endTimeWithGrace = $endTime->copy()->addMinutes(15);

        if (! $attendance) {
            // No clock-in: check if past checkout time with grace
            if (now()->greaterThan($endTimeWithGrace)) {
                Attendance::create([
                    'intern_id' => $intern->user_id,
                    'office_id' => $intern->office_id ?? 1,
                    'clock_in' => Carbon::today()->setTimeFromTimeString($effectiveSchedule['start_time'] ?? '08:00:00'),
                    'clock_out' => $endTime,
                    'status' => 'absent',
                    'remarks' => 'Absent for the day',
                ]);
                $absentCount++;
            }
        } elseif ($attendance && ! $attendance->clock_out) {
            // Active clock-in but no clock-out: check if past checkout time with grace
            if (now()->greaterThan($endTimeWithGrace)) {
                // Perform auto clock-out using AttendanceService to ensure hours rendering and journals are correctly written
                $attendanceService->recordAttendance(
                    $intern->user_id,
                    $attendance->latitude,
                    $attendance->longitude,
                    'System Auto Clock-Out (Forgot to clock out)'
                );
                $autoLogoutCount++;
            }
        }
    }

    $this->info("Successfully logged {$absentCount} absent records and auto-clocked out {$autoLogoutCount} interns.");
})->purpose('Mark active interns as absent or auto-checkout if shift has ended');

Schedule::command('app:mark-absent')->everyFifteenMinutes()->withoutOverlapping();
