<?php

namespace App\Console\Commands;

use App\Models\OnboardingApplicant;
use App\Services\SmartMatchingService;
use Illuminate\Console\Command;

class RunMatchingCommand extends Command
{
    protected $signature = 'matching:run {id?}';

    protected $description = 'Manually run matching for a specific applicant or all applicants';

    public function handle(SmartMatchingService $service)
    {
        $id = $this->argument('id');

        if ($id) {
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
        } else {
            $applicants = OnboardingApplicant::all();
            $this->info('Running matching calculations for ' . $applicants->count() . ' applicants...');

            foreach ($applicants as $applicant) {
                $this->info(' - Running match for: ' . $applicant->full_name);
                $service->runMatchingForApplicant($applicant);
            }

            $this->info('Success! Matching calculations complete for all applicants.');
        }
    }
}
