<?php

namespace App\Console\Commands;

use App\Models\OnboardingApplicant;
use App\Services\SmartMatchingService;
use Illuminate\Console\Command;

class RunMatchingCommand extends Command
{
    protected $signature = 'matching:run {id}';

    protected $description = 'Manually run matching for an applicant';

    public function handle(SmartMatchingService $service)
    {
        $id = $this->argument('id');
        $applicant = OnboardingApplicant::find($id);

        if (! $applicant) {
            $this->error('Applicant not found.');

            return;
        }

        $this->info('Running matching for: '.$applicant->full_name);
        $result = $service->runMatchingForApplicant($applicant);

        if ($result) {
            $this->info('Success!');
        } else {
            $this->error('Failed. Check logs.');
        }
    }
}
