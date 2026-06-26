<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Events\AccountLocked;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AnomalyDetectionService;
use App\Services\SecurityAuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Vinkla\Hashids\Facades\Hashids;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        // Consolidate counts into a single query and cache for 2 minutes
        $stats = Cache::remember('security_monitor_counts', 120, function () {
            return AuditLog::selectRaw('
                COUNT(CASE WHEN anomaly_score <= 0.5 THEN 1 END) as normal,
                COUNT(CASE WHEN anomaly_score > 0.5 AND anomaly_score <= 0.7 THEN 1 END) as warning,
                COUNT(CASE WHEN anomaly_score > 0.7 THEN 1 END) as critical
            ')->first();
        });

        $normalCount = $stats->normal;
        $warningCount = $stats->warning;
        $criticalCount = $stats->critical;

        $query = AuditLog::with(['user', 'internDetails', 'supervisorDetails']);

        // Filtering Logic
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'auth':
                    $query->whereIn('action', [
                        'Authentication', 'Failed Authentication',
                        'Login', 'Failed Login',
                        'MFA Enabled', 'MFA Disabled', 'MFA Setup Started',
                    ]);
                    break;
                case 'onboarding':
                    $query->whereIn('action', ['Applicant Evaluation', 'Intern Matched', 'Matching Results Cleared', 'Intern Endorsed']);
                    break;
                case 'tasks':
                    $query->where(function ($q) {
                        $q->where('action', 'like', '%Task%')
                            ->orWhere('action', 'like', '%Submission%');
                    });
                    break;
                case 'admin':
                    $query->where(function ($q) {
                        $q->where('action', 'like', '%User%')
                            ->orWhere('action', 'like', '%Backup%')
                            ->orWhere('action', 'like', '%Export%')
                            ->orWhere('action', 'like', '%Configuration%');
                    });
                    break;
            }
        }

        $securityLogs = $query->latest('timestamp')->paginate(10);

        // Calculate anomaly counts and 24h persistence for these users (Feng & Zhang, 2023)
        $userIds = $securityLogs->pluck('user_id')->filter()->unique();
        $userMetrics = [];
        if ($userIds->isNotEmpty()) {
            $metricsRaw = AuditLog::whereIn('user_id', $userIds)
                ->selectRaw('
                    user_id,
                    COUNT(CASE WHEN anomaly_score > 0.5 THEN 1 END) as total_anomalies,
                    COUNT(CASE WHEN anomaly_score > 0.5 AND timestamp >= ? THEN 1 END) as persistence_24h
                ', [now()->subDay()])
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            foreach ($userIds as $uid) {
                $m = $metricsRaw->get($uid);
                $userMetrics[$uid] = [
                    'count' => $m ? $m->total_anomalies : 0,
                    'persistence' => $m ? $m->persistence_24h : 0,
                ];
            }
        }

        // --- Fetch Admin Evaluation Log Trail ---
        // 1. Fetch DB logs
        $evalLogsDb = AuditLog::with('user')
            ->where('action', 'like', 'Evaluated Anomaly:%')
            ->latest('timestamp')
            ->limit(30)
            ->get();

        // 2. Fetch File logs (Super Admin evaluations)
        $evalLogsFile = [];
        $logPath = storage_path('logs/secure_audit.log');
        if (\Illuminate\Support\Facades\File::exists($logPath)) {
            try {
                $lines = \Illuminate\Support\Facades\File::lines($logPath);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $decoded = json_decode($line, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (isset($decoded['action']) && str_starts_with($decoded['action'], 'Evaluated Anomaly:')) {
                            $evalLogsFile[] = $decoded;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        // 3. Unify, extract targets and sort
        $unifiedLogs = [];
        $targetUserIds = [];
        $targetLogIds = [];

        $parseResource = function ($resource) {
            if (preg_match('/User:\s*([A-Za-z0-9]+),\s*Log:\s*([A-Za-z0-9]+)/', $resource, $matches)) {
                return [
                    'user_id' => Hashids::decode($matches[1])[0] ?? null,
                    'log_id' => Hashids::decode($matches[2])[0] ?? null,
                ];
            }
            return null;
        };

        foreach ($evalLogsDb as $log) {
            $parsed = $parseResource($log->resource);
            $targetUid = $parsed ? $parsed['user_id'] : null;
            $targetLid = $parsed ? $parsed['log_id'] : null;
            
            if ($targetUid) $targetUserIds[] = $targetUid;
            if ($targetLid) $targetLogIds[] = $targetLid;

            $unifiedLogs[] = (object) [
                'timestamp' => \Carbon\Carbon::parse($log->timestamp),
                'evaluator_id' => $log->user_id,
                'evaluator_email' => $log->user->email ?? 'Unknown Admin',
                'evaluator_role' => $log->user ? ($log->user->role_id->label() ?? 'Admin') : 'Admin',
                'action' => $log->action,
                'target_user_id' => $targetUid,
                'target_log_id' => $targetLid,
                'ip_address' => $log->ip_address,
                'device' => str_replace('|', ' • ', $log->device ?? 'System'),
                'user_agent' => $log->user_agent,
            ];
        }

        foreach ($evalLogsFile as $log) {
            $parsed = $parseResource($log['resource'] ?? '');
            $targetUid = $parsed ? $parsed['user_id'] : null;
            $targetLid = $parsed ? $parsed['log_id'] : null;
            
            if ($targetUid) $targetUserIds[] = $targetUid;
            if ($targetLid) $targetLogIds[] = $targetLid;

            $unifiedLogs[] = (object) [
                'timestamp' => \Carbon\Carbon::parse($log['timestamp']),
                'evaluator_id' => $log['user_id'],
                'evaluator_email' => $log['email'] ?? 'Super Admin',
                'evaluator_role' => 'Super Admin',
                'action' => $log['action'],
                'target_user_id' => $targetUid,
                'target_log_id' => $targetLid,
                'ip_address' => $log['ip_address'] ?? null,
                'device' => str_replace('|', ' • ', AuditLog::parseDeviceFromUserAgent($log['user_agent'] ?? '')),
                'user_agent' => $log['user_agent'] ?? null,
            ];
        }

        // Sort unified logs descending by timestamp
        usort($unifiedLogs, function ($a, $b) {
            return $b->timestamp->timestamp <=> $a->timestamp->timestamp;
        });

        // Limit to latest 30
        $unifiedLogs = array_slice($unifiedLogs, 0, 30);

        // Batch query targets
        $targetUserIds = array_filter(array_unique($targetUserIds));
        $targetLogIds = array_filter(array_unique($targetLogIds));

        $targetUsers = collect();
        if (!empty($targetUserIds)) {
            $targetUsers = User::withoutGlobalScopes()
                ->with(['internDetails', 'supervisorDetails'])
                ->whereIn('id', $targetUserIds)
                ->get()
                ->keyBy('id');
        }

        $targetLogs = collect();
        if (!empty($targetLogIds)) {
            $targetLogs = AuditLog::whereIn('log_id', $targetLogIds)
                ->get()
                ->keyBy('log_id');
        }

        return view('admin.security-monitor', compact(
            'normalCount', 'warningCount', 'criticalCount', 'securityLogs', 'userMetrics',
            'unifiedLogs', 'targetUsers', 'targetLogs'
        ));
    }

    public function runAnalysis(AnomalyDetectionService $service)
    {
        try {
            $lockKey = 'recalculate_anomaly_scores_lock';
            $lock = Cache::lock($lockKey, 60);
            $maxWait = 15; // Max wait 15 seconds
            $waited = 0;

            // Try to acquire the lock, waiting if necessary
            while (! $lock->get() && $waited < $maxWait) {
                sleep(1);
                $waited++;
            }

            try {
                // Now that we have the lock (or gave up waiting), run the service logic
                $service->recalculateScores();

                // Clear counts cache so UI reflects new scores immediately
                Cache::forget('security_monitor_counts');

                // Set session flag to indicate analysis has been run
                session(['security_analysis_run' => true]);
            } finally {
                optional($lock)->release();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Anomaly scores recalculated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to run analysis: '.$e->getMessage(),
            ], 500);
        }
    }

    public function export()
    {
        $logs = AuditLog::with(['user', 'internDetails', 'supervisorDetails'])
            ->latest('timestamp')
            ->get();

        $data = [
            'logs' => $logs,
            'date' => now()->format('F d, Y H:i:s'),
        ];

        $pdf = Pdf::loadView('exports.security-audit-pdf', $data);
        $pdf->setPaper('a4', 'landscape'); // Better for wide tables

        return $pdf->download('SmarTern-Security-Audit-'.now()->format('Ymd').'.pdf');
    }

    public function downloadGuide()
    {
        $pdf = Pdf::loadView('exports.security-documentation');
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('SmarTern-Security-Technical-Specification.pdf');
    }

    public function markSafe(User $user, AuditLog $log, Request $request, AnomalyDetectionService $service, SecurityAuditService $auditService): RedirectResponse
    {
        $authUser = auth()->user();
        $userRoleVal = $user->role_id instanceof UserRole ? $user->role_id->value : (int)$user->role_id;
        $authRoleVal = $authUser->role_id instanceof UserRole ? $authUser->role_id->value : (int)$authUser->role_id;

        if ($userRoleVal === UserRole::SUPER_ADMIN->value) {
            return back()->with('error', 'Cannot perform action on a super administrator account.');
        }

        if ($userRoleVal === UserRole::ADMIN->value && $authRoleVal !== UserRole::SUPER_ADMIN->value) {
            return back()->with('error', 'Cannot perform action on an administrator account.');
        }

        $user->update([
            'locked_until' => null,
        ]);

        $service->warmBaseline($log);

        $auditService->log(
            'Evaluated Anomaly: Marked Safe',
            'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
            auth()->id()
        );

        return back()->with('success', 'Anomaly evaluated as safe. User baseline updated.');
    }

    public function markCompromised(User $user, AuditLog $log, Request $request, SecurityAuditService $auditService): RedirectResponse
    {
        $authUser = auth()->user();
        $userRoleVal = $user->role_id instanceof UserRole ? $user->role_id->value : (int)$user->role_id;
        $authRoleVal = $authUser->role_id instanceof UserRole ? $authUser->role_id->value : (int)$authUser->role_id;

        if ($userRoleVal === UserRole::SUPER_ADMIN->value) {
            return back()->with('error', 'Cannot perform action on a super administrator account.');
        }

        if ($userRoleVal === UserRole::ADMIN->value && $authRoleVal !== UserRole::SUPER_ADMIN->value) {
            return back()->with('error', 'Cannot perform action on an administrator account.');
        }

        $user->update([
            'locked_until' => null,
            'is_first_login' => true,
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
        ]);

        try {
            broadcast(new AccountLocked($user));
        } catch (\Exception $e) {
            // Suppress broadcast errors when offline
        }

        $auditService->log(
            'Evaluated Anomaly: Compromised Account - Credentials Reset',
            'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
            auth()->id()
        );

        return back()->with('success', 'Anomaly marked as compromised. User credentials and MFA reset.');
    }

    public function markSuspended(User $user, AuditLog $log, Request $request, SecurityAuditService $auditService): RedirectResponse
    {
        $authUser = auth()->user();
        $userRoleVal = $user->role_id instanceof UserRole ? $user->role_id->value : (int)$user->role_id;
        $authRoleVal = $authUser->role_id instanceof UserRole ? $authUser->role_id->value : (int)$authUser->role_id;

        if ($userRoleVal === UserRole::SUPER_ADMIN->value) {
            return back()->with('error', 'Cannot perform action on a super administrator account.');
        }

        if ($userRoleVal === UserRole::ADMIN->value && $authRoleVal !== UserRole::SUPER_ADMIN->value) {
            return back()->with('error', 'Cannot perform action on an administrator account.');
        }

        $user->update([
            'locked_until' => '9999-12-31 23:59:59',
        ]);

        try {
            broadcast(new AccountLocked($user));
        } catch (\Exception $e) {
            // Suppress broadcast errors when offline
        }

        $auditService->log(
            'Evaluated Anomaly: Malicious Insider - Suspended Account',
            'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
            auth()->id()
        );

        return back()->with('success', 'Anomaly marked as malicious insider. User suspended indefinitely.');
    }
}
