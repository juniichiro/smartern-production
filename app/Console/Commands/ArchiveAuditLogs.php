<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:archive-audit-logs {--days=90 : The number of days of hot logs to retain in the database}')]
#[Description('Archive audit logs older than the specified retention period into a compressed Gzip file and prune them from the database.')]
class ArchiveAuditLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $this->error('The retention period must be a positive integer.');

            return 1;
        }

        $cutoff = now()->subDays($days);
        $this->info("Archiving and pruning audit logs older than {$days} days (cutoff: {$cutoff->toDateTimeString()})...");

        // Ensure target directory exists
        $archiveDirectory = storage_path('app/audit_archives');
        if (! is_dir($archiveDirectory)) {
            mkdir($archiveDirectory, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $tempJsonPath = "{$archiveDirectory}/audit_logs_archive_{$timestamp}.json";
        $gzPath = "{$tempJsonPath}.gz";

        $totalArchived = 0;
        $fileHandle = fopen($tempJsonPath, 'w');

        if (! $fileHandle) {
            $this->error('Failed to open archive file for writing.');

            return 1;
        }

        // Start JSON array
        fwrite($fileHandle, '[');

        $first = true;

        // Chunk query to avoid memory exhaustion
        AuditLog::where('timestamp', '<', $cutoff)
            ->orderBy('log_id', 'asc')
            ->chunk(1000, function ($logs) use ($fileHandle, &$totalArchived, &$first) {
                foreach ($logs as $log) {
                    if (! $first) {
                        fwrite($fileHandle, ',');
                    }
                    fwrite($fileHandle, json_encode($log->toArray()));
                    $first = false;
                    $totalArchived++;
                }
            });

        // Close JSON array
        fwrite($fileHandle, ']');
        fclose($fileHandle);

        if ($totalArchived === 0) {
            if (file_exists($tempJsonPath)) {
                unlink($tempJsonPath);
            }
            $this->info('No audit logs found older than the specified retention threshold.');

            return 0;
        }

        // Compress the JSON file to Gzip
        $this->info("Compressing {$totalArchived} logs into Gzip archive...");
        $gzFile = gzopen($gzPath, 'w9'); // w9 is maximum compression
        if (! $gzFile) {
            $this->error('Failed to open Gzip file for compression.');
            if (file_exists($tempJsonPath)) {
                unlink($tempJsonPath);
            }

            return 1;
        }

        $jsonContent = file_get_contents($tempJsonPath);
        gzwrite($gzFile, $jsonContent);
        gzclose($gzFile);

        // Delete uncompressed file
        unlink($tempJsonPath);

        $this->info("Successfully created archive at: {$gzPath}");

        // Chunk prune matching records to avoid table lock and transaction log overflow
        $this->info('Pruning archived logs from the database...');
        $prunedCount = 0;

        while (true) {
            $deleted = AuditLog::where('timestamp', '<', $cutoff)
                ->limit(1000)
                ->delete();

            $prunedCount += $deleted;
            if ($deleted < 1000) {
                break;
            }
        }

        $this->info("Successfully archived and pruned {$prunedCount} audit logs.");

        return 0;
    }
}
