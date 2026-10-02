<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class InternAttendanceStateService
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Resolve the intern's real attendance state for today
     *
     * @param User $user
     * @return array
     */
    public function resolveState(User $user): array
    {
        $statusData = $this->attendanceService->getInternStatus($user->id);
        $todayRecord = $statusData['todayRecord'] ?? null;
        $completedToday = $statusData['completedToday'] ?? false;
        $effectiveSchedule = $statusData['effectiveSchedule'] ?? [];
        $canScan = $statusData['canScan'] ?? true;

        $serverNow = now()->timezone('Asia/Manila');

        // State 1: Timed Out / Day Complete
        if ($completedToday && $todayRecord && $todayRecord->clock_out) {
            $clockIn = Carbon::parse($todayRecord->clock_in)->timezone('Asia/Manila');
            $clockOut = Carbon::parse($todayRecord->clock_out)->timezone('Asia/Manila');
            $durationMinutes = $clockIn->diffInMinutes($clockOut);

            // Standard lunch break deduction if > 5 hours
            if ($durationMinutes > 300) {
                $durationMinutes = max(0, $durationMinutes - 60);
            }

            $hours = floor($durationMinutes / 60);
            $mins = $durationMinutes % 60;
            $formattedDuration = sprintf('%dh %02dm', $hours, $mins);

            return [
                'state' => 'day_complete',
                'headline' => "Today: {$formattedDuration}",
                'subtext' => "Day complete · {$clockIn->format('g:i A')} — {$clockOut->format('g:i A')}",
                'duration' => $formattedDuration,
                'clock_in_formatted' => $clockIn->format('g:i A'),
                'clock_out_formatted' => $clockOut->format('g:i A'),
                'clock_in_epoch_ms' => $clockIn->getTimestamp() * 1000,
                'server_now_epoch_ms' => $serverNow->getTimestamp() * 1000,
                'elapsed_seconds' => 0,
                'button_label' => null,
                'button_action' => null,
                'button_url' => null,
                'can_scan' => false,
                'status_pill' => [
                    'label' => 'Day complete',
                    'color' => 'emerald',
                ],
            ];
        }

        // State 2: Currently Timed In (Awaiting clock-out)
        if ($todayRecord && !$todayRecord->clock_out) {
            $clockIn = Carbon::parse($todayRecord->clock_in)->timezone('Asia/Manila');
            $elapsedSeconds = max(0, $serverNow->diffInSeconds($clockIn));
            $hours = floor($elapsedSeconds / 3600);
            $mins = floor(($elapsedSeconds % 3600) / 60);
            $formattedElapsed = sprintf('%dh %02dm', $hours, $mins);

            return [
                'state' => 'timed_in',
                'headline' => "Timed in at {$clockIn->format('g:i A')}",
                'subtext' => 'Active shift in progress',
                'duration' => null,
                'clock_in_formatted' => $clockIn->format('g:i A'),
                'clock_out_formatted' => null,
                'clock_in_epoch_ms' => $clockIn->getTimestamp() * 1000,
                'server_now_epoch_ms' => $serverNow->getTimestamp() * 1000,
                'elapsed_seconds' => $elapsedSeconds,
                'elapsed_formatted' => $formattedElapsed,
                'button_label' => 'Time out',
                'button_action' => 'time_out',
                'button_url' => route('intern.attendance'),
                'can_scan' => true,
                'status_pill' => [
                    'label' => 'Active shift',
                    'color' => 'blue',
                ],
            ];
        }

        // State 3: Unavailable (Blocked by schedule, holiday, weekend, or ended)
        $isBlocked = (!empty($effectiveSchedule['blocked'])) || (!$canScan);
        if ($isBlocked) {
            $reason = $effectiveSchedule['description'] ?? ($statusData['statusMessage'] ?? 'Attendance is unavailable for today.');

            return [
                'state' => 'unavailable',
                'headline' => 'Attendance unavailable',
                'subtext' => $reason,
                'duration' => null,
                'clock_in_formatted' => null,
                'clock_out_formatted' => null,
                'clock_in_epoch_ms' => 0,
                'server_now_epoch_ms' => $serverNow->getTimestamp() * 1000,
                'elapsed_seconds' => 0,
                'button_label' => 'Time in unavailable',
                'button_action' => null,
                'button_url' => null,
                'can_scan' => false,
                'status_pill' => [
                    'label' => 'Unavailable',
                    'color' => 'slate',
                ],
            ];
        }

        // State 4: Not Timed In (Ready to time in)
        return [
            'state' => 'not_timed_in',
            'headline' => "You haven't timed in today",
            'subtext' => 'Ready to log your training session',
            'duration' => null,
            'clock_in_formatted' => null,
            'clock_out_formatted' => null,
            'clock_in_epoch_ms' => 0,
            'server_now_epoch_ms' => $serverNow->getTimestamp() * 1000,
            'elapsed_seconds' => 0,
            'button_label' => 'Time in',
            'button_action' => 'time_in',
            'button_url' => route('intern.attendance'),
            'can_scan' => true,
            'status_pill' => [
                'label' => 'Not timed in',
                'color' => 'slate',
            ],
        ];
    }

    /**
     * Calculate weekly hours rendered for this intern
     */
    public function calculateWeeklyHours(User $user): float
    {
        $startOfWeek = now()->timezone('Asia/Manila')->startOfWeek(Carbon::MONDAY);
        $endOfWeek = now()->timezone('Asia/Manila')->endOfWeek(Carbon::SUNDAY);

        $weekLogs = Attendance::where('intern_id', $user->id)
            ->whereBetween('clock_in', [$startOfWeek, $endOfWeek])
            ->whereNotNull('clock_out')
            ->get();

        $totalMinutes = 0;
        foreach ($weekLogs as $log) {
            $in = Carbon::parse($log->clock_in);
            $out = Carbon::parse($log->clock_out);
            $diff = $in->diffInMinutes($out);
            if ($diff > 300) {
                $diff = max(0, $diff - 60);
            }
            $totalMinutes += min(480, $diff); // Cap at 8 hours per day standard
        }

        return round($totalMinutes / 60, 1);
    }

    /**
     * Calculate pace estimate if sufficient history exists
     */
    public function calculatePaceEstimate(User $user, float $renderedHours, float $totalRequired): ?string
    {
        $remaining = max(0, $totalRequired - $renderedHours);
        if ($remaining <= 0) {
            return null;
        }

        $intern = $user->internDetails;
        if (!$intern || !$intern->start_date) {
            return null;
        }

        $startDate = Carbon::parse($intern->start_date)->startOfDay();
        $now = now()->timezone('Asia/Manila')->startOfDay();
        $weeksElapsed = $startDate->diffInWeeks($now);

        // Require at least 2 weeks of history
        if ($weeksElapsed < 2 || $renderedHours <= 0) {
            return null;
        }

        $avgHoursPerWeek = $renderedHours / $weeksElapsed;
        if ($avgHoursPerWeek < 4) {
            return null;
        }

        $weeksLeft = (int) ceil($remaining / $avgHoursPerWeek);
        if ($weeksLeft <= 0) {
            return null;
        }

        return "At this pace, about {$weeksLeft} weeks left";
    }
}
