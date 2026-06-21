<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Events\AccountLocked;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AnomalyDetectionService;
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

        return view('admin.security-monitor', compact(
            'normalCount', 'warningCount', 'criticalCount', 'securityLogs', 'userMetrics'
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

    public function markSafe(User $user, AuditLog $log, Request $request, AnomalyDetectionService $service): RedirectResponse
    {
        if ($user->role_id === UserRole::ADMIN) {
            return back()->with('error', 'Cannot perform action on an administrator account.');
        }

        $user->update([
            'locked_until' => null,
        ]);

        $service->warmBaseline($log);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Evaluated Anomaly: Marked Safe',
            'resource' => 'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => AuditLog::parseDeviceFromUserAgent($request->userAgent()),
        ]);

        return back()->with('success', 'Anomaly evaluated as safe. User baseline updated.');
    }

    public function markCompromised(User $user, AuditLog $log, Request $request): RedirectResponse
    {
        if ($user->role_id === UserRole::ADMIN) {
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

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Evaluated Anomaly: Compromised Account - Credentials Reset',
            'resource' => 'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => AuditLog::parseDeviceFromUserAgent($request->userAgent()),
        ]);

        return back()->with('success', 'Anomaly marked as compromised. User credentials and MFA reset.');
    }

    public function markSuspended(User $user, AuditLog $log, Request $request): RedirectResponse
    {
        if ($user->role_id === UserRole::ADMIN) {
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

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Evaluated Anomaly: Malicious Insider - Suspended Account',
            'resource' => 'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => AuditLog::parseDeviceFromUserAgent($request->userAgent()),
        ]);

        return back()->with('success', 'Anomaly marked as malicious insider. User suspended indefinitely.');
    }
}
