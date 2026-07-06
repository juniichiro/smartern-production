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
                    if ($criticalLogs->isNotEmpty()) {
                        foreach ($criticalLogs as $log) {
                            $userRoleVal = $log->user->role_id instanceof UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                            if ($log->user && $userRoleVal !== UserRole::SUPER_ADMIN->value && config('services.security.lockout_enabled', true)) {
                                $log->user->update([
                                    'locked_until' => now()->addMinutes(30),
                                ]);

                                try {
                                    broadcast(new AccountLocked($log->user));
                                } catch (\Exception $e) {
                                    // Suppress broadcast errors if Reverb/Echo is offline
                                }
                            }

                            // 2. Send notifications to all Admins and Super Admins (bypassing global scopes)
                            $recipients = User::withoutGlobalScopes()
                                ->whereIn('role_id', [UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])
                                ->get();

                            foreach ($recipients as $recipient) {
                                $recipientRoleVal = $recipient->role_id instanceof UserRole ? $recipient->role_id->value : (int)$recipient->role_id;
                                // If the user who triggered the critical log is an Admin, only notify the Super Admin (root)
                                if ($userRoleVal === UserRole::ADMIN->value && $recipientRoleVal !== UserRole::SUPER_ADMIN->value) {
                                    continue;
                                }

                                $notification = Notification::create([
                                    'recipient_id' => $recipient->id,
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
                                    // Suppress socket broadcasting errors
                                }

                                // Trigger email notification
                                try {
                                    Mail::to($recipient->email)->send(new SecurityAlertMail($log, $recipient));
                                } catch (\Exception $e) {
                                    Log::error("Failed to send security alert email to {$recipient->email}: ".$e->getMessage());
                                }
                            }
                        }
                    }

                    if ($warningLogs->isNotEmpty()) {
                        foreach ($warningLogs as $log) {
                            $userRoleVal = $log->user->role_id instanceof UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                            $recipients = User::withoutGlobalScopes()
                                ->whereIn('role_id', [UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])
                                ->get();

                            foreach ($recipients as $recipient) {
                                $recipientRoleVal = $recipient->role_id instanceof UserRole ? $recipient->role_id->value : (int)$recipient->role_id;
                                // If the flagged user is an Admin, only notify Super Admins
                                if ($userRoleVal === UserRole::ADMIN->value && $recipientRoleVal !== UserRole::SUPER_ADMIN->value) {
                                    continue;
                                }

                                $notification = Notification::create([
                                    'recipient_id' => $recipient->id,
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
                            }
                        }
                    }
                }

                return count($ids);
            }
        }

        return 0;
    }

    /**
     * Scores a single log entry synchronously, saving the score,
     * and taking appropriate action if it is flagged.
     */
    public function scoreSingleLog(AuditLog $log)
    {
        $this->ensureDaemonIsRunning();

        // Convert the single log to a Collection so existing getScores methods work
        $logs = collect([$log]);

        $scores = $this->getScoresFromDaemon($logs);
        if ($scores === null) {
            $scores = $this->getScoresFromSubprocess($logs);
        }

        if (! empty($scores) && isset($scores[0])) {
            $score = max(0, min(0.999, (float) $scores[0]));

            $log->update([
                'anomaly_score' => $score,
            ]);

            Cache::forget('security_monitor_counts');

            // Handle actions based on the score
            if ($score > 0.7) {
                $log->load('user');
                $userRoleVal = $log->user->role_id instanceof UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                if ($log->user && $userRoleVal !== UserRole::SUPER_ADMIN->value && config('services.security.lockout_enabled', true)) {
                    $log->user->update([
                        'locked_until' => now()->addMinutes(30),
                    ]);

                    try {
                        broadcast(new AccountLocked($log->user));
                    } catch (\Exception $e) {
                        // Suppress broadcast errors
                    }
                }

                // Send notifications to all Admins and Super Admins
                $recipients = User::withoutGlobalScopes()
                    ->whereIn('role_id', [UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])
                    ->get();

                foreach ($recipients as $recipient) {
                    $recipientRoleVal = $recipient->role_id instanceof UserRole ? $recipient->role_id->value : (int)$recipient->role_id;
                    if ($userRoleVal === UserRole::ADMIN->value && $recipientRoleVal !== UserRole::SUPER_ADMIN->value) {
                        continue;
                    }

                    $notification = Notification::create([
                        'recipient_id' => $recipient->id,
                        'sender_id' => $log->user_id,
                        'type' => 'Security Alert',
                        'category' => 'system',
                        'message' => 'Critical anomaly detected: user '.($log->user->email ?? 'ID '.$log->user_id)." performed action '{$log->action}' (Score: ".number_format($score, 2).')',
                        'target_url' => '/admin/security',
                        'is_read' => false,
                    ]);

                    try {
                        broadcast(new RealTimeNotificationSent($notification));
                    } catch (\Exception $e) {}

                    try {
                        Mail::to($recipient->email)->send(new SecurityAlertMail($log, $recipient));
                    } catch (\Exception $e) {
                        Log::error("Failed to send security alert email to {$recipient->email}: ".$e->getMessage());
                    }
                }
            } elseif ($score > 0.5) {
                $log->load('user');
                $userRoleVal = $log->user->role_id instanceof UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                $recipients = User::withoutGlobalScopes()
                    ->whereIn('role_id', [UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])
                    ->get();

                foreach ($recipients as $recipient) {
                    $recipientRoleVal = $recipient->role_id instanceof UserRole ? $recipient->role_id->value : (int)$recipient->role_id;
                    if ($userRoleVal === UserRole::ADMIN->value && $recipientRoleVal !== UserRole::SUPER_ADMIN->value) {
                        continue;
                    }

                    $notification = Notification::create([
                        'recipient_id' => $recipient->id,
                        'sender_id' => $log->user_id,
                        'type' => 'Security Warning',
                        'category' => 'system',
                        'message' => 'Warning anomaly detected: user '.($log->user->email ?? 'ID '.$log->user_id)." performed action '{$log->action}' (Score: ".number_format($score, 2).')',
                        'target_url' => '/admin/security',
                        'is_read' => false,
                    ]);

                    try {
                        broadcast(new RealTimeNotificationSent($notification));
                    } catch (\Exception $e) {}
                }
            }
        }
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
            $pythonPath = config('services.python.path') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3');
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
        $pythonPath = config('services.python.path') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3');
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

    /**
     * Seeds the baseline for a specific user based on their role and current login credentials.
     * This avoids the cold-start problem and prevents false-positive warnings.
     */
    public function seedUserBaseline(User $user, string $ip, string $userAgent, bool $force = false)
    {
        $userId = $user->id;
        $roleId = (int) $user->role_id->value;

        $cacheKey = "user_baseline_seeded_{$userId}";
        if (!$force && Cache::has($cacheKey)) {
            return;
        }

        $path = storage_path('app/audit_baseline.json');
        $baseline = [];

        if (file_exists($path)) {
            $baseline = json_decode(file_get_contents($path), true) ?? [];
        }

        if ($force) {
            // Remove existing baseline logs for this user to allow clean overwrite
            $baseline = array_values(array_filter($baseline, fn($log) => !isset($log['user_id']) || (int)$log['user_id'] !== $userId));
        } else {
            // Check if logs already exist for this user in the baseline file
            $hasLogs = false;
            foreach ($baseline as $log) {
                if (isset($log['user_id']) && (int)$log['user_id'] === $userId) {
                    $hasLogs = true;
                    break;
                }
            }

            if ($hasLogs) {
                Cache::forever($cacheKey, true);
                return;
            }
        }

        // Templates of normal actions per role to seed
        $roleTemplates = [
            5 => ['Authentication', 'Clock In', 'Clock Out', 'Task Submitted', 'Journal Entry', 'Journal Updated', 'Accomplishment Export'], // Intern
            1 => ['Authentication', 'Account Created', 'Account Updated', 'Geofence Updated', 'Backup Initiated'], // Admin
            2 => ['Authentication', 'Applicant Evaluation', 'Match Finalized', 'Batch Matching Upload'], // Head Supervisor
            3 => ['Authentication', 'Task Assigned', 'Task Updated', 'Task Status Updated', 'Announcement Created'], // Supervisor
            4 => ['Authentication', 'Intern Progress Viewed', 'Report Downloaded'], // Coordinator
        ];

        $actions = $roleTemplates[$roleId] ?? ['Authentication'];

        // Append mock baseline entries for this user
        foreach ($actions as $action) {
            // Generate multiple logs per action to establish a stable count
            $repeat = $action === 'Authentication' ? 5 : 3;
            for ($i = 0; $i < $repeat; $i++) {
                $baseline[] = [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'action' => $action,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'timestamp' => now()->subDays(rand(1, 10))->toDateTimeString(),
                ];
            }
        }

        // Keep baseline at a manageable size (last 4000 entries max to prevent excessive file sizes)
        if (count($baseline) > 4000) {
            $baseline = array_slice($baseline, -4000);
        }

        file_put_contents($path, json_encode($baseline, JSON_PRETTY_PRINT));
        Cache::forever($cacheKey, true);
    }
}
