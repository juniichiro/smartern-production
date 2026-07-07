<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedMLBaseline extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'app:seed-ml-baseline {--ip=130.105.10.15 : The IP address to use for normal baseline activity}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Seed the audit logs with a historical baseline of healthy activity to train the ML model.';

    /**
     * Execute the console command
     */
    public function handle()
    {
        $this->info('Starting ML Baseline Generation (to Local File)...');

        $usersByRole = User::all()->groupBy(fn ($u) => (int) $u->role_id->value);

        if ($usersByRole->isEmpty() || ! isset($usersByRole[1])) {
            $this->error('No admin user found to attribute logs to.');

            return 1;
        }

        $baselineData = [
            // Intern Activity (Intern role)
            ['action' => 'Authentication', 'count' => 400, 'role' => 5],
            ['action' => 'Clock In', 'count' => 120, 'role' => 5],
            ['action' => 'Clock Out', 'count' => 120, 'role' => 5],
            ['action' => 'Task Submitted', 'count' => 100, 'role' => 5],
            ['action' => 'Journal Entry', 'count' => 100, 'role' => 5],
            ['action' => 'Journal Updated', 'count' => 40, 'role' => 5],
            ['action' => 'Accomplishment Export', 'count' => 20, 'role' => 5],
            ['action' => 'MFA Setup Started', 'count' => 40, 'role' => 5],
            ['action' => 'MFA Enabled', 'count' => 40, 'role' => 5],
            ['action' => 'MFA Disabled', 'count' => 10, 'role' => 5],
            ['action' => 'Geofence Violation', 'count' => 20, 'role' => 5],

            // Admin Activity (Admin role)
            ['action' => 'Authentication', 'count' => 200, 'role' => 1],
            ['action' => 'Account Created', 'count' => 60, 'role' => 1],
            ['action' => 'Account Updated', 'count' => 40, 'role' => 1],
            ['action' => 'Account Deleted', 'count' => 10, 'role' => 1],
            ['action' => 'Geofence Updated', 'count' => 20, 'role' => 1],
            ['action' => 'Backup Initiated', 'count' => 20, 'role' => 1],
            ['action' => 'System Rollback', 'count' => 10, 'role' => 1],
            ['action' => 'MFA Setup Started', 'count' => 40, 'role' => 1],
            ['action' => 'MFA Enabled', 'count' => 40, 'role' => 1],
            ['action' => 'MFA Disabled', 'count' => 10, 'role' => 1],
            ['action' => 'Bulk Import', 'count' => 10, 'role' => 1],
            ['action' => 'Account Reactivated', 'count' => 20, 'role' => 1],
            ['action' => 'Account Deactivated', 'count' => 20, 'role' => 1],
            ['action' => 'Onboarding Request Updated', 'count' => 30, 'role' => 1],
            ['action' => 'Resolved Anomaly: Marked Safe', 'count' => 20, 'role' => 1],
            ['action' => 'Resolved Anomaly: Marked Compromised', 'count' => 10, 'role' => 1],
            ['action' => 'Resolved Anomaly: Marked Suspended', 'count' => 10, 'role' => 1],

            // Head Supervisor Activity (Head Supervisor role)
            ['action' => 'Authentication', 'count' => 100, 'role' => 2],
            ['action' => 'Applicant Evaluation', 'count' => 120, 'role' => 2],
            ['action' => 'Match Finalized', 'count' => 60, 'role' => 2],
            ['action' => 'Match Results Cleared', 'count' => 20, 'role' => 2],
            ['action' => 'MFA Setup Started', 'count' => 40, 'role' => 2],
            ['action' => 'MFA Enabled', 'count' => 40, 'role' => 2],
            ['action' => 'MFA Disabled', 'count' => 10, 'role' => 2],
            ['action' => 'Batch Matching Upload', 'count' => 10, 'role' => 2],
            ['action' => 'Match Unfinalized', 'count' => 10, 'role' => 2],

            // Supervisor Activity (Supervisor role)
            ['action' => 'Authentication', 'count' => 100, 'role' => 3],
            ['action' => 'Task Assigned', 'count' => 80, 'role' => 3],
            ['action' => 'Task Updated', 'count' => 40, 'role' => 3],
            ['action' => 'Task Status Updated', 'count' => 80, 'role' => 3],
            ['action' => 'Task Deleted', 'count' => 10, 'role' => 3],
            ['action' => 'Announcement Created', 'count' => 30, 'role' => 3],
            ['action' => 'Announcement Updated', 'count' => 20, 'role' => 3],
            ['action' => 'Announcement Deleted', 'count' => 10, 'role' => 3],
            ['action' => 'MFA Setup Started', 'count' => 40, 'role' => 3],
            ['action' => 'MFA Enabled', 'count' => 40, 'role' => 3],
            ['action' => 'MFA Disabled', 'count' => 10, 'role' => 3],

            // Coordinator Activity (Coordinator role)
            ['action' => 'Authentication', 'count' => 100, 'role' => 4],
            ['action' => 'Intern Progress Viewed', 'count' => 80, 'role' => 4],
            ['action' => 'Report Downloaded', 'count' => 40, 'role' => 4],
            ['action' => 'MFA Setup Started', 'count' => 40, 'role' => 4],
            ['action' => 'MFA Enabled', 'count' => 40, 'role' => 4],
            ['action' => 'MFA Disabled', 'count' => 10, 'role' => 4],

            ['action' => 'Failed Authentication', 'count' => 20, 'role' => 1],
        ];

        $primaryIp = $this->option('ip');
        
        // Standard Philippine carrier IP subnets (Smart, Globe, PLDT, Converge, Dito, GOMO, TNT)
        $phCarrierSubnets = [
            '110.54',   // Smart Communications Mobile (TNT / Sun)
            '121.54',   // Smart Communications Mobile (TNT / Sun)
            '49.144',   // Smart Communications Mobile (TNT)
            '203.87',   // Smart Communications Cellular (TNT)
            '112.198',  // Globe Telecom Mobile (GOMO)
            '180.190',  // Globe Telecom Mobile (GOMO Data)
            '122.54',   // Globe Telecom Broadband (GOMO)
            '49.145',   // Globe Telecom Home Broadband
            '136.158',  // Globe / Sky Broadband
            '120.28',   // PLDT Home Fibr
            '112.200',  // PLDT Home Broadband
            '130.105',  // Converge ICT Home Broadband
            '180.191',  // Dito Telecommunity Mobile
        ];

        // Standard Philippine carrier IPs to simulate normal network variance
        $secondaryIps = [
            '110.54.166.230',  // Smart Mobile
            '112.198.85.12',   // Globe Mobile
            '120.28.64.99',    // PLDT Broadband
            '49.145.15.22',    // Globe Home
            '130.105.10.15',   // Converge Home
        ];

        $defaultUserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $logs = [];
        foreach ($baselineData as $config) {
            $roleId = $config['role'];
            // Get all users in the DB for this role, fallback to Admin if none found
            $roleUsers = isset($usersByRole[$roleId]) ? $usersByRole[$roleId]->all() : [$usersByRole[1]->first()];

            for ($i = 0; $i < $config['count']; $i++) {
                $rand = rand(1, 100);
                // Assign to one of the users in this role randomly
                $selectedUser = $roleUsers[array_rand($roleUsers)];

                if ($roleId === 4 || $roleId === 5) {
                    // Externals (Coordinators & Interns): twenty percent primary office IP, eighty percent distributed/randomized carrier IPs (reflects remote & mobile data usage)
                    if ($rand <= 20) {
                        $assignedIp = $primaryIp;
                    } elseif ($rand <= 50) {
                        $assignedIp = $secondaryIps[array_rand($secondaryIps)];
                    } else {
                        // Generate a randomized IP within typical Philippine carrier subnets
                        $subnet = $phCarrierSubnets[array_rand($phCarrierSubnets)];
                        $assignedIp = $subnet . '.' . rand(0, 255) . '.' . rand(1, 254);
                    }
                } else {
                    // Internal Staff (Admins, Head Supervisors, Supervisors): eighty-five percent primary office IP, fifteen percent secondary carrier pools
                    if ($rand <= 85) {
                        $assignedIp = $primaryIp;
                    } else {
                        $assignedIp = $secondaryIps[array_rand($secondaryIps)];
                    }
                }

                $logs[] = [
                    'user_id' => $selectedUser->id,
                    'role_id' => $roleId,
                    'action' => $config['action'],
                    'ip_address' => $assignedIp,
                    'user_agent' => $defaultUserAgent,
                ];
            }
        }

        $filePath = storage_path('app/audit_baseline.json');
        file_put_contents($filePath, json_encode($logs, JSON_PRETTY_PRINT));

        $this->info('Successfully generated '.count($logs)." historical logs in {$filePath}.");
        $this->info('The ML engine will now use this file as a baseline during analysis.');

        return 0;
    }
}
