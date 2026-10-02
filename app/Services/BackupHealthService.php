<?php

namespace App\Services;

use App\Models\SystemBackup;
use Carbon\Carbon;

class BackupHealthService
{
    /**
     * Get the latest backup record.
     */
    public function getLatestBackup(): ?SystemBackup
    {
        return SystemBackup::latest()->first();
    }

    /**
     * Get the latest successful backup record.
     */
    public function getLastSuccessfulBackup(): ?SystemBackup
    {
        return SystemBackup::where('status', 'success')
            ->latest()
            ->first();
    }

    /**
     * Check if a backup is currently in progress.
     */
    public function hasRunningBackup(): bool
    {
        if (session('backup_in_progress')) {
            return true;
        }

        return SystemBackup::where('status', 'in_progress')->exists();
    }

    /**
     * Get the calculated backup health state.
     */
    public function getHealthState(): array
    {
        $warnHours = (int) config('backup.health.warn_after_hours', 36);
        $criticalHours = (int) config('backup.health.critical_after_hours', 72);

        $latestBackup = $this->getLatestBackup();
        $lastSuccessful = $this->getLastSuccessfulBackup();
        $isRunning = $this->hasRunningBackup();
        $totalSuccessfulCount = SystemBackup::where('status', 'success')->count();

        // Next scheduled run computation
        $nextRun = $this->calculateNextRun();

        // 1. Running state
        if ($isRunning) {
            return [
                'status' => 'running',
                'level' => 'info',
                'headline' => 'Backup in progress',
                'detail' => 'A system backup snapshot is currently generating...',
                'last_run' => $latestBackup?->created_at,
                'last_run_diff' => $latestBackup?->created_at?->diffForHumans(),
                'next_run' => $nextRun,
                'next_run_display' => $this->formatNextRunDisplay($nextRun),
                'latest_backup' => $latestBackup,
                'total_successful' => $totalSuccessfulCount,
                'is_stale' => false,
                'is_critical' => false,
            ];
        }

        // 2. Empty state (No backups exist at all)
        if (! $latestBackup) {
            return [
                'status' => 'empty',
                'level' => 'warning',
                'headline' => 'No backups yet',
                'detail' => 'Your system does not have any backup snapshots recorded. Create a backup now to protect your data.',
                'last_run' => null,
                'last_run_diff' => null,
                'next_run' => $nextRun,
                'next_run_display' => $this->formatNextRunDisplay($nextRun),
                'latest_backup' => null,
                'total_successful' => 0,
                'is_stale' => false,
                'is_critical' => false,
            ];
        }

        // 3. Failed latest run
        if ($latestBackup->status === 'failed') {
            return [
                'status' => 'failed',
                'level' => 'failed',
                'headline' => 'The last backup failed',
                'detail' => 'The automated or manual snapshot encountered an error and did not complete.',
                'last_run' => $latestBackup->created_at,
                'last_run_diff' => $latestBackup->created_at->diffForHumans(),
                'next_run' => $nextRun,
                'next_run_display' => $this->formatNextRunDisplay($nextRun),
                'latest_backup' => $latestBackup,
                'total_successful' => $totalSuccessfulCount,
                'is_stale' => false,
                'is_critical' => true,
            ];
        }

        // 4. Stale / Critical / Healthy states based on last successful backup
        if (! $lastSuccessful) {
            return [
                'status' => 'critical',
                'level' => 'critical',
                'headline' => 'No successful backups found',
                'detail' => 'All previous backup attempts have failed or been removed.',
                'last_run' => $latestBackup->created_at,
                'last_run_diff' => $latestBackup->created_at->diffForHumans(),
                'next_run' => $nextRun,
                'next_run_display' => $this->formatNextRunDisplay($nextRun),
                'latest_backup' => $latestBackup,
                'total_successful' => 0,
                'is_stale' => true,
                'is_critical' => true,
            ];
        }

        $now = Carbon::now();
        $hoursSinceLastSuccess = $lastSuccessful->created_at->diffInHours($now);
        $daysSinceLastSuccess = $lastSuccessful->created_at->diffInDays($now);

        if ($hoursSinceLastSuccess >= $criticalHours) {
            $dayCount = (int) $daysSinceLastSuccess;
            $dayText = $dayCount === 1 ? '1 day' : "{$dayCount} days";

            return [
                'status' => 'critical',
                'level' => 'critical',
                'headline' => "Last successful backup was {$dayText} ago",
                'detail' => "Automated backups appear interrupted. Last verified snapshot was created {$lastSuccessful->created_at->format('M d, Y h:i A')}.",
                'last_run' => $lastSuccessful->created_at,
                'last_run_diff' => $lastSuccessful->created_at->diffForHumans(),
                'next_run' => $nextRun,
                'next_run_display' => $this->formatNextRunDisplay($nextRun),
                'latest_backup' => $latestBackup,
                'total_successful' => $totalSuccessfulCount,
                'is_stale' => true,
                'is_critical' => true,
            ];
        }

        if ($hoursSinceLastSuccess >= $warnHours) {
            return [
                'status' => 'stale',
                'level' => 'warning',
                'headline' => "Last successful backup was {$hoursSinceLastSuccess} hours ago",
                'detail' => "Backups are lagging behind schedule. Last verified snapshot was {$lastSuccessful->created_at->diffForHumans()}.",
                'last_run' => $lastSuccessful->created_at,
                'last_run_diff' => $lastSuccessful->created_at->diffForHumans(),
                'next_run' => $nextRun,
                'next_run_display' => $this->formatNextRunDisplay($nextRun),
                'latest_backup' => $latestBackup,
                'total_successful' => $totalSuccessfulCount,
                'is_stale' => true,
                'is_critical' => false,
            ];
        }

        // 5. Healthy state
        return [
            'status' => 'healthy',
            'level' => 'success',
            'headline' => 'Backups are healthy · last successful backup '.$lastSuccessful->created_at->diffForHumans(),
            'detail' => 'All scheduled data snapshots and public storage archives are intact and up to date.',
            'last_run' => $lastSuccessful->created_at,
            'last_run_diff' => $lastSuccessful->created_at->diffForHumans(),
            'next_run' => $nextRun,
            'next_run_display' => $this->formatNextRunDisplay($nextRun),
            'latest_backup' => $latestBackup,
            'total_successful' => $totalSuccessfulCount,
            'is_stale' => false,
            'is_critical' => false,
        ];
    }

    /**
     * Compute next scheduled run based on configuration.
     */
    public function calculateNextRun(): ?Carbon
    {
        $enabled = config('backup.schedule.enabled', true);
        if (! $enabled) {
            return null;
        }

        $time = config('backup.schedule.time', '19:00');
        [$hours, $minutes] = explode(':', $time);

        $now = Carbon::now();
        $targetToday = $now->copy()->setTime((int) $hours, (int) $minutes, 0);

        if ($now->lessThan($targetToday)) {
            return $targetToday;
        }

        return $targetToday->addDay();
    }

    /**
     * Format next run display text cleanly.
     */
    public function formatNextRunDisplay(?Carbon $nextRun): ?string
    {
        if (! $nextRun) {
            return null;
        }

        $now = Carbon::now();
        $timeStr = $nextRun->format('g:i A');

        if ($nextRun->isToday()) {
            return "Today at {$timeStr}";
        }

        if ($nextRun->isTomorrow()) {
            return "Tomorrow at {$timeStr}";
        }

        return $nextRun->format('M d, Y \a\t g:i A');
    }

    /**
     * Aggregate counts for filter pills using a single query.
     */
    public function getCounts(): array
    {
        $result = SystemBackup::selectRaw("
            count(*) as total,
            coalesce(sum(case when backup_type = 'auto' then 1 else 0 end), 0) as auto_count,
            coalesce(sum(case when backup_type = 'manual' then 1 else 0 end), 0) as manual_count,
            coalesce(sum(case when status = 'failed' then 1 else 0 end), 0) as failed_count
        ")->first();

        return [
            'all' => (int) ($result->total ?? 0),
            'auto' => (int) ($result->auto_count ?? 0),
            'manual' => (int) ($result->manual_count ?? 0),
            'failed' => (int) ($result->failed_count ?? 0),
        ];
    }

    /**
     * Determine if a backup is the only successful backup in the system.
     */
    public function isOnlySuccessfulBackup(SystemBackup $backup): bool
    {
        if ($backup->status !== 'success') {
            return false;
        }

        return SystemBackup::where('status', 'success')->count() === 1;
    }
}
