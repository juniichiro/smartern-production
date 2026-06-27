<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SecurityAuditService
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Records a security action in the audit log and triggers ML analysis.
     *
     * @param  string  $action  The action performed (e.g., 'Clock-In', 'Task Submission')
     * @param  string  $resource  The specific resource affected (e.g., 'Attendance Record #12', 'Task: Weekly Report')
     * @param  int|null  $userId  Optional user ID (defaults to current authenticated user)
     */
    public function log($action, $resource, $userId = null)
    {
        $userId = $userId ?? Auth::id();

        if (! $userId) {
            return null; // Don't log if there's no user context
        }

        // Check if performing user is Super Admin
        $user = User::withoutGlobalScopes()->find($userId);
        if ($user && (($user->role_id === UserRole::SUPER_ADMIN) || (is_numeric($user->role_id) && (int)$user->role_id === UserRole::SUPER_ADMIN->value))) {
            // Write root logs to secure server file strictly
            $logPath = storage_path('logs/secure_audit.log');
            $logEntry = json_encode([
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $userId,
                'email' => $user->email,
                'action' => $action,
                'resource' => $resource,
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
            ]) . "\n";
            \Illuminate\Support\Facades\File::append($logPath, $logEntry);
            return null;
        }

        // 1. Create the log entry (Instant)
        $log = AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'resource' => $resource,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'anomaly_score' => null,
            'timestamp' => now(),
        ]);

        // 2. Score anomaly synchronously for instant threat locking
        try {
            app(\App\Services\AnomalyDetectionService::class)->scoreSingleLog($log);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed synchronous anomaly detection scoring: " . $e->getMessage());
        }

        return $log;
    }
}
