<?php

namespace App\Console\Commands;

use App\Models\SystemBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RestoreBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore the system to a specific backup snapshot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupId = $this->argument('id');
        $backup = SystemBackup::find($backupId);

        if (! $backup) {
            $msg = "Backup record #{$backupId} not found.";
            $this->error($msg);
            Log::error($msg);

            return 1;
        }

        if (! Storage::exists($backup->file_path)) {
            $msg = "Backup file not found at: {$backup->file_path}";
            $this->error($msg);
            Log::error($msg);

            return 1;
        }

        $fullPath = Storage::path($backup->file_path);

        // Calculate and validate SHA-256 hash to prevent tampering
        $calculatedHash = hash_file('sha256', $fullPath);
        $storedHash = $backup->sha256_hash;

        if ($storedHash && $calculatedHash !== $storedHash) {
            $msg = "SECURITY ALERT: Backup file integrity check failed! File hash: {$calculatedHash}, Expected: {$storedHash}. Restoration aborted to prevent tampering.";
            $this->error($msg);
            Log::critical($msg);

            return 1;
        }

        $this->info("Backup SHA-256 hash verified: {$calculatedHash}");
        $this->info("Restoring from snapshot: {$backup->file_path}...");
        Log::info("Initiating system restoration from: {$backup->file_path}");

        // Manually append MySQL bin path to ensure mysql CLI is accessible on Windows
        $mysqlPath = 'C:\Program Files\MySQL\MySQL Server 8.0\bin';
        $currentPath = getenv('PATH');
        if (strpos($currentPath, $mysqlPath) === false) {
            putenv("PATH={$currentPath};{$mysqlPath}");
        }

        $zip = new ZipArchive;
        if ($zip->open($fullPath) === true) {

            // 1. Restore Database
            $driver = config('database.default');
            if ($driver === 'sqlite') {
                $dbName = config('database.connections.sqlite.database');
                $this->info('Restoring SQLite database...');

                // Disconnect to release any potential file locks
                DB::disconnect();

                // Delete WAL and SHM files if they exist to prevent them from overwriting the restored base file
                $walFile = $dbName.'-wal';
                $shmFile = $dbName.'-shm';
                if (file_exists($walFile)) {
                    @unlink($walFile);
                }
                if (file_exists($shmFile)) {
                    @unlink($shmFile);
                }

                // Extract from ZIP to a temporary location first
                $tempPath = storage_path('app/temp_restore');
                if (! file_exists($tempPath)) {
                    mkdir($tempPath, 0755, true);
                }

                $zip->extractTo($tempPath, 'database/database.sqlite');
                $extractedDb = $tempPath.'/database/database.sqlite';

                if (file_exists($extractedDb)) {
                    if (copy($extractedDb, $dbName)) {
                        $this->info('Database restored successfully.');
                    } else {
                        $msg = 'Failed to overwrite database file. It might be locked.';
                        $this->error($msg);
                        Log::error($msg);
                        $zip->close();

                        return 1;
                    }
                } else {
                    $msg = 'Extracted database file not found in snapshot.';
                    $this->error($msg);
                    Log::error($msg);
                    $zip->close();

                    return 1;
                }

                // Clean up temp
                @unlink($extractedDb);
                @rmdir($tempPath.'/database');
                @rmdir($tempPath);
            } elseif ($driver === 'mysql') {
                $this->info('Restoring MySQL database...');
                $config = config('database.connections.mysql');
                $tempPath = storage_path('app/temp_restore');
                if (! file_exists($tempPath)) {
                    mkdir($tempPath, 0755, true);
                }

                // Extract SQL file
                $zip->extractTo($tempPath, 'database/database.sql');
                $extractedSql = $tempPath.'/database/database.sql';

                if (file_exists($extractedSql)) {
                    // Construct mysql import command
                    $command = sprintf(
                        'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                        escapeshellarg($config['username']),
                        escapeshellarg($config['password']),
                        escapeshellarg($config['host']),
                        escapeshellarg($config['port']),
                        escapeshellarg($config['database']),
                        escapeshellarg($extractedSql)
                    );

                    exec($command, $output, $returnVar);

                    if ($returnVar === 0) {
                        $this->info('MySQL database imported successfully.');
                    } else {
                        $msg = "Failed to import MySQL database. Ensure the 'mysql' client is installed and accessible.";
                        $this->error($msg);
                        Log::error($msg.' Command exit code: '.$returnVar);
                        $zip->close();

                        return 1;
                    }
                } else {
                    $msg = 'Extracted SQL file not found in snapshot.';
                    $this->error($msg);
                    Log::error($msg);
                    $zip->close();

                    return 1;
                }

                // Clean up temp
                @unlink($extractedSql);
                @rmdir($tempPath.'/database');
                @rmdir($tempPath);
            } else {
                $msg = "Database driver '{$driver}' not supported for automated restoration.";
                $this->warn($msg);
                Log::warning($msg);
            }

            // 2. Restore Storage
            $this->info('Restoring storage files...');
            // We extract everything in the 'storage/' folder of the ZIP
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $zipFilename = $zip->getNameIndex($i);
                if (strpos($zipFilename, 'storage/') === 0 && substr($zipFilename, -1) !== '/') {
                    // Extract to public disk but strip the 'storage/' prefix from the filename
                    $targetPath = substr($zipFilename, strlen('storage/'));

                    $content = $zip->getFromIndex($i);
                    if ($content !== false) {
                        Storage::disk('public')->put($targetPath, $content);
                    }
                }
            }

            $zip->close();

            // 3. Update the restored database to mark THIS backup as success
            // This is needed because the DB inside the ZIP was captured while it was still 'in_progress'
            try {
                $fileSize = $this->formatBytes(Storage::size($backup->file_path));
                SystemBackup::where('backup_id', $backupId)->update([
                    'status' => 'success',
                    'file_size' => $fileSize,
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not update backup status after restoration: '.$e->getMessage());
            }

            $msg = 'System rollback completed successfully.';
            $this->info($msg);
            Log::info($msg);

            return 0;
        } else {
            $msg = 'Failed to open ZIP snapshot.';
            $this->error($msg);
            Log::error($msg);

            return 1;
        }
    }

    /**
     * Format bytes to readable string.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
