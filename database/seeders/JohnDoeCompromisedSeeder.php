<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JohnDoeCompromisedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find John Doe
        $user = User::withoutGlobalScopes()->where('email', 'john.doe@smartern.com')->first();

        if (!$user) {
            $this->command->error('User john.doe@smartern.com not found. Please run UserSeeder first.');
            return;
        }

        // Clean up existing logs for John Doe to make test data clean
        AuditLog::where('user_id', $user->id)->delete();

        // 2. Add multiple recent normal logs
        $normalIp = '120.28.64.99'; // Normal PLDT Broadband
        $normalUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $normalDevice = 'Windows|Chrome';

        $logs = [
            [
                'user_id' => $user->id,
                'action' => 'Authentication',
                'resource' => 'Successful login via web',
                'ip_address' => $normalIp,
                'user_agent' => $normalUA,
                'device' => $normalDevice,
                'anomaly_score' => 0.120,
                'timestamp' => Carbon::now()->subHours(24),
            ],
            [
                'user_id' => $user->id,
                'action' => 'Clock In',
                'resource' => 'Clocked in at Office',
                'ip_address' => $normalIp,
                'user_agent' => $normalUA,
                'device' => $normalDevice,
                'anomaly_score' => 0.080,
                'timestamp' => Carbon::now()->subHours(23),
            ],
            [
                'user_id' => $user->id,
                'action' => 'Journal Entry',
                'resource' => 'Created journal entry for work',
                'ip_address' => $normalIp,
                'user_agent' => $normalUA,
                'device' => $normalDevice,
                'anomaly_score' => 0.050,
                'timestamp' => Carbon::now()->subHours(18),
            ],
            [
                'user_id' => $user->id,
                'action' => 'Clock Out',
                'resource' => 'Clocked out from Office',
                'ip_address' => $normalIp,
                'user_agent' => $normalUA,
                'device' => $normalDevice,
                'anomaly_score' => 0.090,
                'timestamp' => Carbon::now()->subHours(15),
            ],
            [
                'user_id' => $user->id,
                'action' => 'Authentication',
                'resource' => 'Successful login via web',
                'ip_address' => $normalIp,
                'user_agent' => $normalUA,
                'device' => $normalDevice,
                'anomaly_score' => 0.110,
                'timestamp' => Carbon::now()->subHours(2),
            ],
        ];

        foreach ($logs as $logData) {
            AuditLog::create($logData);
        }

        // 3. Add the flagged critical log (compromised/anomalous access)
        $anomalousIp = '198.51.100.77'; // Anomalous remote IP
        $anomalousUA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';
        $anomalousDevice = 'MacOS|Safari';

        $flaggedLog = AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Authentication',
            'resource' => 'Successful login via web (Suspicious Location)',
            'ip_address' => $anomalousIp,
            'user_agent' => $anomalousUA,
            'device' => $anomalousDevice,
            'anomaly_score' => 0.895, // High anomaly score > 0.7 to trigger lockout logic
            'timestamp' => Carbon::now()->subMinutes(10),
        ]);

        // 4. Lock the user's account to enable the "Evaluate" workflow
        $user->update([
            'locked_until' => Carbon::now()->addMinutes(30),
        ]);

        $this->command->info('Successfully seeded John Doe compromised logs and locked the account.');
    }
}
