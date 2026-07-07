<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Enums\UserRole;
use App\Events\AccountLocked;
use App\Events\RealTimeNotificationSent;
use App\Mail\SecurityAlertMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SimulateAnomaly extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'app:simulate-anomaly {user_id : The ID of the user to log the anomaly for} {--score=0.85 : The anomaly score to assign} {--action=Authentication : The simulated action name} {--ip=203.87.154.22 : The IP address to log} {--lockout : Whether to also trigger the automatic lockout and admin alerts}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Simulate a critical security anomaly for testing and presentation purposes.';

    /**
     * Execute the console command
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $score = (float) $this->option('score');
        $action = $this->option('action');
        $ip = $this->option('ip');
        $lockout = $this->option('lockout');

        $this->info("Simulating anomaly for user {$user->email}...");

        // Create the Audit Log entry with the high anomaly score
        $log = AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'resource' => 'Simulated Threat Context',
            'ip_address' => $ip,
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'anomaly_score' => $score,
            'timestamp' => now(),
        ]);

        $this->info("Created Audit Log entry (ID: {$log->log_id}) with anomaly score: {$score}");

        if ($lockout && $score > 0.70) {
            $this->info("Locking user account and sending admin notifications...");

            $userRoleVal = $user->role_id instanceof UserRole ? $user->role_id->value : (int)$user->role_id;
            if ($userRoleVal !== UserRole::SUPER_ADMIN->value) {
                $user->update([
                    'locked_until' => now()->addMinutes(30),
                ]);

                try {
                    broadcast(new AccountLocked($user));
                } catch (\Exception $e) {}
            }

            // Create notification for admins
            $admins = User::withoutGlobalScopes()
                ->whereIn('role_id', [UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])
                ->get();

            foreach ($admins as $admin) {
                $notification = Notification::create([
                    'recipient_id' => $admin->id,
                    'sender_id' => $user->id,
                    'type' => 'Security Alert',
                    'category' => 'system',
                    'message' => "Simulated Critical anomaly detected: user {$user->email} performed action '{$action}' (Score: " . number_format($score, 2) . ")",
                    'target_url' => '/admin/security',
                    'is_read' => false,
                ]);

                try {
                    broadcast(new RealTimeNotificationSent($notification));
                } catch (\Exception $e) {}

                try {
                    Mail::to($admin->email)->send(new SecurityAlertMail($log, $admin));
                } catch (\Exception $e) {
                    $this->warn("Failed to send email to {$admin->email}: " . $e->getMessage());
                }
            }
        }

        $this->info("Successfully simulated anomaly event!");
        return 0;
    }
}
