<?php

namespace App\Console\Commands;

use App\Services\AnomalyDetectionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('app:recalculate-anomaly-scores')]
#[Description('Command description')]
class RecalculateAnomalyScores extends Command
{
    /**
     * Execute the console command.
     */
    // Inside the handle() method
    public function handle(AnomalyDetectionService $service)
    {
        $lock = Cache::lock('recalculate_anomaly_scores_lock', 60);

        if (! $lock->get()) {
            $this->info('Command is already running.');

            return;
        }

        try {
            $count = $service->recalculateScores();
            if ($count > 0) {
                $this->info("Successfully recalculated {$count} scores.");
            } else {
                $this->info('No logs to recalculate or analysis failed.');
            }
        } finally {
            $lock->release();
        }
    }
}
