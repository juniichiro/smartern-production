<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AnomalyDetectionService;
use Illuminate\Console\Command;

class WarmUserBaseline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:warm-user-baseline {user_id : The ID of the user to warm baseline for} {--ip= : Custom IP address to seed} {--user-agent= : Custom User-Agent string to seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed/warm the ML anomaly baseline for a specific user to prevent false positive warning triggers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        // Get custom or fallback parameters
        $ip = $this->option('ip') ?: '127.0.0.1';
        $userAgent = $this->option('user-agent') ?: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

        $roleName = $user->role ? $user->role->name : 'Unknown';
        $this->info("Warming baseline for user {$user->email} (Role: {$roleName})...");

        app(AnomalyDetectionService::class)->seedUserBaseline($user, $ip, $userAgent, true);

        $this->info("Successfully seeded baseline logs for user ID {$userId}!");
        return 0;
    }
}
