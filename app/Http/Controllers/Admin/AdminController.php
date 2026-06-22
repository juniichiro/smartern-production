<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGeofenceRequest;
use App\Http\Requests\Admin\UploadBackupRequest;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Office;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FALaravel\Google2FA;
use ZipArchive;

use Vinkla\Hashids\Facades\Hashids;

class AdminController extends Controller
{
    protected $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        // Fetch Stats
        $totalUsers = User::count();

        // Share the consolidated counts cache with the Security Monitor
        $secStats = Cache::remember('security_monitor_counts', 120, function () {
            return AuditLog::selectRaw('
                COUNT(CASE WHEN anomaly_score <= 0.5 THEN 1 END) as normal,
                COUNT(CASE WHEN anomaly_score > 0.5 AND anomaly_score <= 0.7 THEN 1 END) as warning,
                COUNT(CASE WHEN anomaly_score > 0.7 THEN 1 END) as critical
            ')->first();
        });
        $flaggedLogins = $secStats->critical;

        $totalCertificates = Certificate::count();
        $totalDepartments = Office::count();
        $totalBackups = SystemBackup::count();

        // Cache system health for 5 minutes to avoid redundant checks
        $services = Cache::remember('system_health_stats', 300, function () {
            return $this->getSystemHealth();
        });

        $recentActivities = AuditLog::with(['user', 'internDetails', 'supervisorDetails'])
            ->latest('timestamp')
            ->limit(3)
            ->get();

        // 4. High Risk Anomalies (Score > 0.7)
        $highRiskAnomalies = AuditLog::with(['user', 'internDetails', 'supervisorDetails'])
            ->where('anomaly_score', '>', 0.7)
            ->latest('timestamp')
            ->get()
            ->filter(function ($log) {
                $encodedLogId = Hashids::encode($log->log_id);
                return !AuditLog::where('action', 'like', 'Evaluated Anomaly:%')
                    ->where('resource', 'like', '%' . $encodedLogId . '%')
                    ->exists();
            })
            ->take(5);

        // 5. Recently Created Users
        $newUsers = User::with('role')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'flaggedLogins',
            'totalCertificates',
            'totalDepartments',
            'totalBackups',
            'services',
            'recentActivities',
            'highRiskAnomalies',
            'newUsers'
        ));
    }

    private function getSystemHealth()
    {
        $health = [];

        // 1. Database Health
        try {
            DB::connection()->getPdo();
            $health[] = ['name' => 'Primary Database', 'status' => 'Operational', 'color' => 'green'];
        } catch (\Exception $e) {
            $health[] = ['name' => 'Primary Database', 'status' => 'Down', 'color' => 'rose'];
        }

        // 2. Storage Health
        try {
            $writable = Storage::disk('local')->put('health-check.txt', 'ok');
            if ($writable) {
                Storage::disk('local')->delete('health-check.txt');
                $health[] = ['name' => 'File Storage', 'status' => 'Operational', 'color' => 'green'];
            } else {
                $health[] = ['name' => 'File Storage', 'status' => 'ReadOnly', 'color' => 'orange'];
            }
        } catch (\Exception $e) {
            $health[] = ['name' => 'File Storage', 'status' => 'Error', 'color' => 'rose'];
        }

        // 3. ML Engine Health
        $pythonScript = base_path('scripts/isolation_forest.py');
        if (file_exists($pythonScript)) {
            $health[] = ['name' => 'ML Anomaly Engine', 'status' => 'Operational', 'color' => 'green'];
        } else {
            $health[] = ['name' => 'ML Anomaly Engine', 'status' => 'Missing Script', 'color' => 'rose'];
        }

        // 4. MFA Service
        if (class_exists(Google2FA::class)) {
            $health[] = ['name' => 'MFA Service', 'status' => 'Operational', 'color' => 'green'];
        } else {
            $health[] = ['name' => 'MFA Service', 'status' => 'Not Found', 'color' => 'orange'];
        }

        // 5. Geofencing Service
        $officesWithGeofence = Office::whereNotNull('latitude')->whereNotNull('longitude')->count();
        if ($officesWithGeofence > 0) {
            $health[] = ['name' => 'Geofencing System', 'status' => 'Active', 'color' => 'green'];
        } else {
            $health[] = ['name' => 'Geofencing System', 'status' => 'No Data', 'color' => 'orange'];
        }

        return $health;
    }

    public function configuration()
    {
        $backups = SystemBackup::with('admin')->latest()->paginate(10);

        return view('admin.configuration', compact('backups'));
    }

    public function offices()
    {
        $offices = Office::withCount(['internDetails', 'supervisorDetails'])
            ->with(['supervisorDetails.user'])
            ->get();

        return view('admin.offices', compact('offices'));
    }

    public function updateGeofence(UpdateGeofenceRequest $request)
    {
        if ($request->filled('office_id')) {
            $office = Office::findOrFail($request->office_id);
            $office->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_meters' => $request->radius_meters,
            ]);

            $this->auditService->log('Geofence Updated', "Office '{$office->name}' Radius: {$request->radius_meters}m, Lat: {$request->latitude}, Lng: {$request->longitude}");

            return back()->with('success', "Geofence refined for {$office->name}.");
        }

        Office::query()->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meters' => $request->radius_meters,
        ]);

        $this->auditService->log('Geofence Updated', "Radius: {$request->radius_meters}m, Lat: {$request->latitude}, Lng: {$request->longitude}");

        return back()->with('success', 'Global geofence refined for all offices.');
    }

    public function triggerManualBackup()
    {
        try {
            // Trigger the custom backup command
            Artisan::call('backup:run', [
                '--type' => 'manual',
                '--admin_id' => Auth::id(),
            ]);

            $this->auditService->log('Backup Initiated', 'Manual System Backup');

            return back()->with('success', 'System backup started.');
        } catch (\Exception $e) {
            return back()->with('error', 'We couldn\'t complete the backup: '.$e->getMessage());
        }
    }

    public function uploadSnapshot(UploadBackupRequest $request)
    {
        try {
            $file = $request->file('backup_zip');
            $filename = 'manual_upload_'.now()->format('Y-m-d_H-i-s').'.zip';
            $backupPath = 'backups/'.$filename;

            // 1. Temporary save to validate contents
            $tempPath = $file->getPathname();
            $zip = new ZipArchive;

            if ($zip->open($tempPath) !== true) {
                return back()->with('error', 'We couldn\'t open that ZIP file. Please check if it\'s a valid archive.');
            }

            // 2. Validate structure (Must have database/ folder)
            $hasDatabase = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (strpos($zip->getNameIndex($i), 'database/') === 0) {
                    $hasDatabase = true;
                    break;
                }
            }
            $zip->close();

            if (! $hasDatabase) {
                return back()->with('error', 'That backup file is missing the required internal structure.');
            }

            // 3. Save to storage
            $file->storeAs('backups', $filename, 'local');

            // 4. Create record
            $bytes = $file->getSize();
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= (1 << (10 * $pow));
            $formattedSize = round($bytes, 2).' '.$units[$pow];

            SystemBackup::create([
                'admin_id' => Auth::id(),
                'backup_type' => 'manual',
                'file_path' => $backupPath,
                'file_size' => $formattedSize,
                'status' => 'success',
            ]);

            return back()->with('success', 'Backup snapshot saved.');
        } catch (\Exception $e) {
            return back()->with('error', 'We couldn\'t save the backup file: '.$e->getMessage());
        }
    }

    public function restoreSnapshot(SystemBackup $backup)
    {
        try {
            $id = $backup->getKey();
            $exitCode = Artisan::call('backup:restore', ['id' => $id]);

            if ($exitCode === 0) {
                $this->auditService->log('System Rollback', "Restored from Backup #{$id}");
                // Logout the user and clear session since the DB was replaced
                Auth::logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect()->route('login')->with('success', 'System restored to the selected snapshot. Please log in again.');
            }

            return back()->with('error', 'Rollback failed. Please check system logs.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during rollback: '.$e->getMessage());
        }
    }

    public function downloadBackup(SystemBackup $backup)
    {
        if ($backup->file_path && Storage::exists($backup->file_path)) {
            return Storage::download($backup->file_path);
        }

        return back()->with('error', 'Backup file not found.');
    }

    public function destroyBackup(SystemBackup $backup)
    {
        if ($backup->file_path && Storage::exists($backup->file_path)) {
            Storage::delete($backup->file_path);
        }
        $backup->delete();

        return back()->with('success', 'Backup record withdrawn.');
    }
}
