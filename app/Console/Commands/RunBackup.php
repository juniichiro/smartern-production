<?php

namespace App\Console\Commands;

use App\Models\SystemBackup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RunBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {--type=manual} {--admin_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a system backup (Database and storage files)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $adminId = $this->option('admin_id') ?: (Auth::check() ? Auth::id() : null);
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "smartern_backup_{$timestamp}.zip";
        $backupPath = "backups/{$filename}";

        $this->info("Starting {$type} backup...");
        Log::info("Starting {$type} system backup: {$filename}");

        // Manually append MySQL bin path to ensure mysqldump is accessible on Windows
        $mysqlPath = 'C:\Program Files\MySQL\MySQL Server 8.0\bin';
        $currentPath = getenv('PATH');
        if (strpos($currentPath, $mysqlPath) === false) {
            putenv("PATH={$currentPath};{$mysqlPath}");
        }

        // Ensure the backups directory exists
        if (! Storage::disk('local')->exists('backups')) {
            Storage::disk('local')->makeDirectory('backups');
        }

        // 1. Create Initial Record (so it's in the DB backup)
        $backupRecord = SystemBackup::create([
            'admin_id' => $adminId,
            'backup_type' => $type === 'auto' ? 'auto' : 'manual',
            'file_path' => $backupPath,
            'status' => 'in_progress',
        ]);

        $zip = new ZipArchive;
        $fullPath = Storage::disk('local')->path($backupPath);

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

            // 2. Backup Database
            $driver = config('database.default');
            if ($driver === 'sqlite') {
                $dbName = config('database.connections.sqlite.database');
                if (file_exists($dbName)) {
                    $zip->addFile($dbName, 'database/database.sqlite');
                    $this->info('SQLite database backed up successfully.');
                } else {
                    $msg = "SQLite database file not found at: {$dbName}";
                    $this->error($msg);
                    Log::error($msg);
                    $zip->close();
                    $backupRecord->update(['status' => 'failed']);

                    return 1;
                }
            } elseif ($driver === 'mysql') {
                $config = config('database.connections.mysql');
                $tempSqlPath = storage_path('app/temp_db_export.sql');

                // Determine binary path
                $mysqldumpBin = 'mysqldump';
                if (file_exists('/usr/bin/mysqldump') && is_executable('/usr/bin/mysqldump')) {
                    $mysqldumpBin = '/usr/bin/mysqldump';
                }

                // Construct mysqldump command
                $command = sprintf(
                    '%s --user=%s --password=%s --host=%s --port=%s %s > %s',
                    escapeshellarg($mysqldumpBin),
                    escapeshellarg($config['username']),
                    escapeshellarg($config['password']),
                    escapeshellarg($config['host']),
                    escapeshellarg($config['port']),
                    escapeshellarg($config['database']),
                    escapeshellarg($tempSqlPath)
                );

                exec($command, $output, $returnVar);

                if ($returnVar === 0 && file_exists($tempSqlPath)) {
                    $zip->addFile($tempSqlPath, 'database/database.sql');
                    $this->info('MySQL database exported successfully.');
                } else {
                    $msg = "MySQL export failed. Ensure 'mysqldump' is installed and accessible.";
                    $this->error($msg);
                    Log::error($msg.' Command exit code: '.$returnVar);
                    $zip->close();
                    if (file_exists($tempSqlPath)) {
                        @unlink($tempSqlPath);
                    }
                    $backupRecord->update(['status' => 'failed']);

                    return 1;
                }

                // Note: We don't delete tempSqlPath here yet because zip->close() needs it.
                // We'll clean it up after closing.
            }

            // 3. Backup Storage
            $this->info('Backing up storage files...');
            $disk = Storage::disk('public');
            $files = $disk->allFiles();
            $tempFilesToClean = [];

            foreach ($files as $file) {
                if (config('filesystems.disks.public.driver') === 's3') {
                    $tempLocalFile = tempnam(sys_get_temp_dir(), 's3_zip_');
                    file_put_contents($tempLocalFile, $disk->get($file));
                    $zip->addFile($tempLocalFile, 'storage/'.$file);
                    $tempFilesToClean[] = $tempLocalFile;
                } else {
                    $zip->addFile($disk->path($file), 'storage/'.$file);
                }
            }

            $zip->close();

            // Cleanup S3 temp files
            if (!empty($tempFilesToClean)) {
                foreach ($tempFilesToClean as $tempFile) {
                    if (file_exists($tempFile)) {
                        @unlink($tempFile);
                    }
                }
            }

            // Cleanup temp SQL if it exists
            if (isset($tempSqlPath) && file_exists($tempSqlPath)) {
                @unlink($tempSqlPath);
            }

            // 4. Calculate SHA-256 integrity hash
            $hash = hash_file('sha256', $fullPath);
            $this->info("Backup SHA-256 hash: {$hash}");
            Log::info("Backup SHA-256 integrity hash: {$hash}");

            // Update Record to Success
            $fileSize = $this->formatBytes(Storage::disk('local')->size($backupPath));
            $backupRecord->update([
                'file_size' => $fileSize,
                'status' => 'success',
                'sha256_hash' => $hash,
            ]);

            $msg = "Backup completed: {$filename} ({$fileSize}) (Hash: {$hash})";
            $this->info($msg);
            Log::info($msg);

            return 0;
        } else {
            $msg = 'Failed to create ZIP file.';
            $this->error($msg);
            Log::error($msg);
            $backupRecord->update(['status' => 'failed']);

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
