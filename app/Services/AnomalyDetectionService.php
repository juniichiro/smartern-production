<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Events\AccountLocked;
use App\Events\RealTimeNotificationSent;
use App\Mail\SecurityAlertMail;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class AnomalyDetectionService
{
    public function recalculateScores()
    {
        // 1. Proactively ensure the daemon is running for maximum performance
        $this->ensureDaemonIsRunning();

        // 2. Fetch logs that need scoring (select anomaly_score as well).
        $logs = AuditLog::with('user:id,role_id')->latest('timestamp')
            ->select('log_id', 'user_id', 'action', 'ip_address', 'user_agent', 'device', 'anomaly_score', 'timestamp')
            ->limit(500)
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        // Check if there are any unscored logs in the batch
        $hasUnscored = $logs->contains(function ($log) {
            return $log->anomaly_score === null;
        });

        if (! $hasUnscored) {
            return 0;
        }

        $scores = $this->getScoresFromDaemon($logs);

        // Fallback to subprocess if daemon fails
        if ($scores === null) {
            $scores = $this->getScoresFromSubprocess($logs);
        }

        if (! empty($scores)) {
            $ids = [];
            $cases = [];

            foreach ($scores as $index => $score) {
                if (isset($logs[$index])) {
                    $log = $logs[$index];

                    // Only update logs that have not been scored yet (anomaly_score is null)
                    if ($log->anomaly_score === null) {
                        $logId = $log->log_id;
                        $finalScore = max(0, min(0.999, (float) $score));

                        $ids[] = $logId;
                        $cases[] = "WHEN log_id = {$logId} THEN {$finalScore}";
                    }
                }
            }

            if (! empty($ids)) {
                $idsList = implode(',', $ids);
                $casesStr = implode(' ', $cases);

                DB::transaction(function () use ($casesStr, $idsList) {
                    DB::update("
                        UPDATE audit_logs 
                        SET anomaly_score = CASE {$casesStr} END 
                        WHERE log_id IN ({$idsList})
                    ");
                });

                Cache::forget('security_monitor_counts');

                // Fetch newly updated logs that have critical anomaly scores (> 0.7)
                $criticalLogs = AuditLog::whereIn('log_id', $ids)
                    ->where('anomaly_score', '>', 0.7)
                    ->with('user')
                    ->get();

                // Fetch newly updated logs that have warning anomaly scores (0.51 - 0.7)
                $warningLogs = AuditLog::whereIn('log_id', $ids)
                    ->whereBetween('anomaly_score', [0.51, 0.7])
                    ->with('user')
                    ->get();

                if ($criticalLogs->isNotEmpty() || $warningLogs->isNotEmpty()) {
                    $admins = User::where('role_id', UserRole::ADMIN)->get();

                    if ($criticalLogs->isNotEmpty()) {
                        foreach ($criticalLogs as $log) {
                            if ($log->user && $log->user->role_id !== UserRole::ADMIN && config('services.security.lockout_enabled', true)) {
                                $log->user->update([
                                    'locked_until' => now()->addMinutes(30),
                                ]);

                                try {
                                    broadcast(new AccountLocked($log->user));
                                } catch (\Exception $e) {
                                    // Suppress broadcast errors if Reverb/Echo is offline
                                }
                            }

                            // 2. Send notifications to all Admins
                            foreach ($admins as $admin) {
                                $notification = Notification::create([
                                    'recipient_id' => $admin->id,
                                    'sender_id' => $log->user_id,
                                    'type' => 'Security Alert',
                                    'category' => 'system',
                                    'message' => 'Critical anomaly detected: user '.($log->user->email ?? 'ID '.$log->user_id)." performed action '{$log->action}' (Score: ".number_format((float) $log->anomaly_score, 2).')',
                                    'target_url' => '/admin/security',
                                    'is_read' => false,
                                ]);

                                // Trigger real-time broadcast
                                try {
                                    broadcast(new RealTimeNotificationSent($notification));
                                } catch (\Exception $e) {
                                    // Suppress socket broadcasting errors in environments where Reverb is offline
                                }

                                // Trigger email notification
                                try {
                                    Mail::to($admin->email)->send(new SecurityAlertMail($log, $admin));
                                } catch (\Exception $e) {
                                    Log::error("Failed to send security alert email to {$admin->email}: ".$e->getMessage());
                                }
                            }
                        }
                    }

                    if ($warningLogs->isNotEmpty()) {
                        foreach ($warningLogs as $log) {
                            foreach ($admins as $admin) {
                                $notification = Notification::create([
                                    'recipient_id' => $admin->id,
                                    'sender_id' => $log->user_id,
                                    'type' => 'Security Warning',
                                    'category' => 'system',
                                    'message' => 'Warning anomaly detected: user '.($log->user->email ?? 'ID '.$log->user_id)." performed action '{$log->action}' (Score: ".number_format((float) $log->anomaly_score, 2).')',
                                    'target_url' => '/admin/security',
                                    'is_read' => false,
                                ]);

                                // Trigger real-time broadcast
                                try {
                                    broadcast(new RealTimeNotificationSent($notification));
                                } catch (\Exception $e) {
                                    // Suppress socket broadcasting errors
                                }

                                // Note: We only send system notifications for warnings per requirements,
                                // but we could also send email if needed. Requirement says "notify admin via system notification/email".
                                // For warning, system notification might be enough to avoid email spam, but let's send email too.
                                try {
                                    Mail::to($admin->email)->send(new SecurityAlertMail($log, $admin));
                                } catch (\Exception $e) {
                                    Log::error("Failed to send security warning email to {$admin->email}: ".$e->getMessage());
                                }
                            }
                        }
                    }
                }

                return count($ids);
            }
        }

        return 0;
    }

    protected function getScoresFromDaemon($logs)
    {
        $host = '127.0.0.1';
        $port = 65432;
        $timeout = 1; // Faster timeout

        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (! $socket) {
                return null;
            }

            $payload = json_encode(['logs' => $logs->toArray()]);
            fwrite($socket, $payload);

            $response = '';
            while (! feof($socket)) {
                $response .= fgets($socket, 4096);
            }
            fclose($socket);

            $data = json_decode($response, true);

            return $data['scores'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Attempts to start the daemon if it's not responding.
     */
    protected function ensureDaemonIsRunning()
    {
        $host = '127.0.0.1';
        $port = 65432;
        $connection = @fsockopen($host, $port, $errno, $errstr, 0.5);

        if (! $connection) {
            $pythonPath = env('PYTHON_PATH', 'python');
            $scriptPath = base_path('scripts/if_daemon.py');

            $command = "{$pythonPath} \"{$scriptPath}\"";

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen('start /B '.$command, 'r'));
            } else {
                exec($command.' > /dev/null 2>&1 &');
            }
        } else {
            fclose($connection);
        }
    }

    protected function getScoresFromSubprocess($logs)
    {
        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('scripts/isolation_forest.py');

        $process = new Process([$pythonPath, $scriptPath]);
        $process->setEnv(['SYSTEMROOT' => getenv('SYSTEMROOT'), 'PATH' => getenv('PATH')]);
        $process->setInput(json_encode(['logs' => $logs->toArray()]));
        $process->run();

        if ($process->isSuccessful()) {
            $output = json_decode($process->getOutput(), true);

            return $output['scores'] ?? null;
        }

        return null;
    }

    /**
     * Appends a trusted log entry to the local baseline file to prevent future false positives.
     * This is typically called after a successful MFA verification.
     */
    public function warmBaseline(AuditLog $log)
    {
        $path = storage_path('app/audit_baseline.json');
        $baseline = [];

        if (file_exists($path)) {
            $baseline = json_decode(file_get_contents($path), true) ?? [];
        }

        // Add the new trusted pattern
        $baseline[] = [
            'user_id' => $log->user_id,
            'action' => $log->action,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'device' => $log->device,
            'timestamp' => $log->timestamp?->toDateTimeString(),
        ];

        // Keep baseline at a manageable size (last 2000 patterns)
        if (count($baseline) > 2000) {
            array_shift($baseline);
        }

        file_put_contents($path, json_encode($baseline, JSON_PRETTY_PRINT));
    }
}
