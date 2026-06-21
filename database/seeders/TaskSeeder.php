<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\InternDetail;
use App\Models\SupervisorDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $interns = InternDetail::all();

        foreach ($interns as $intern) {
            // Find supervisor for this intern's office, or fallback to the standard supervisor
            $supervisorDetail = SupervisorDetail::where('office_id', $intern->office_id)->first();
            $supervisorId = $supervisorDetail ? $supervisorDetail->user_id : 4; // User ID fallback

            // 1. Completed Task 1
            Task::create([
                'supervisor_id' => $supervisorId,
                'intern_id' => $intern->user_id,
                'office_id' => $intern->office_id,
                'title' => 'Initial Orientation and Environment Setup',
                'description' => 'Review the onboarding manuals, install necessary software applications, and configure the project workspace.',
                'status' => 'completed',
                'due_date' => Carbon::now()->subDays(10),
                'remarks' => 'Setup looks clean and verified.',
                'is_file_required' => false,
            ]);

            // 2. Completed Task 2
            Task::create([
                'supervisor_id' => $supervisorId,
                'intern_id' => $intern->user_id,
                'office_id' => $intern->office_id,
                'title' => 'Weekly Progress Presentation',
                'description' => 'Document achievements, issues encountered, and prepare a presentation summarizing accomplishments.',
                'status' => 'completed',
                'due_date' => Carbon::now()->subDays(5),
                'remarks' => 'Great presentation and detail.',
                'is_file_required' => false,
            ]);

            // 3. Pending Task (Awaiting Action)
            Task::create([
                'supervisor_id' => $supervisorId,
                'intern_id' => $intern->user_id,
                'office_id' => $intern->office_id,
                'title' => 'Primary Department Report Drafting',
                'description' => 'Gather logs, summarize performance indicators, and write the draft report for supervisor review.',
                'status' => 'pending',
                'due_date' => Carbon::now()->subDay(),
                'is_file_required' => true,
                'file_path' => 'reports/sample_report.pdf',
            ]);

            // 4. Pending / Revision Task
            $statusOptions = ['pending', 'revision'];
            $selectedStatus = $statusOptions[rand(0, 1)];
            Task::create([
                'supervisor_id' => $supervisorId,
                'intern_id' => $intern->user_id,
                'office_id' => $intern->office_id,
                'title' => 'Final Department Deliverable Review',
                'description' => 'Finalize updates based on department rules and prepare files for the final deployment workflow.',
                'status' => $selectedStatus,
                'due_date' => Carbon::now()->addDays(3),
                'remarks' => $selectedStatus === 'revision' ? 'Please adjust layout formatting.' : null,
                'is_file_required' => false,
            ]);
        }
    }
}
