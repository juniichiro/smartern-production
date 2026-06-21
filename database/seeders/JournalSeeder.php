<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\InternDetail;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $interns = InternDetail::all();

        foreach ($interns as $intern) {
            // Find completed tasks for this intern
            $tasks = Task::where('intern_id', $intern->user_id)->where('status', 'completed')->get();
            $taskIds = $tasks->pluck('id')->toArray();

            // 1. Daily Journal Entry 1 (from 5 days ago)
            Journal::create([
                'intern_id' => $intern->user_id,
                'journal_type' => 'daily',
                'title' => 'Daily Activity Log - ' . Carbon::now()->subDays(5)->format('Y-m-d'),
                'content' => "=== DAILY LOG SUMMARY ===\nWorked on system setup and verified workspace configuration. Fixed folder permissions locally and checked dependencies.",
                'task_ids' => $taskIds,
                'notes' => 'Successfully ran the setup without exceptions.',
                'entry_date' => Carbon::now()->subDays(5),
                'created_at' => Carbon::now()->subDays(5),
            ]);

            // 2. Daily Journal Entry 2 (from 3 days ago)
            Journal::create([
                'intern_id' => $intern->user_id,
                'journal_type' => 'daily',
                'title' => 'Daily Activity Log - ' . Carbon::now()->subDays(3)->format('Y-m-d'),
                'content' => "=== DAILY LOG SUMMARY ===\nDiscussed requirements with supervisor and created draft presentations. Configured DB mapping variables.",
                'task_ids' => [],
                'notes' => 'Discussed database schema adjustments with supervisor.',
                'entry_date' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
            ]);

            // 3. Weekly Journal Entry (from last week)
            Journal::create([
                'intern_id' => $intern->user_id,
                'journal_type' => 'weekly',
                'title' => 'Weekly Performance Log - Week ' . Carbon::now()->subDays(7)->format('W'),
                'content' => "=== WEEKLY LOG SUMMARY ===\nCompleted environment setup, initialized git repository, and drafted first department reports. Attended all stand-up meetings.",
                'task_ids' => $taskIds,
                'notes' => 'Productive week. All core targets achieved on time.',
                'entry_date' => Carbon::now()->subDays(7),
                'created_at' => Carbon::now()->subDays(7),
            ]);
        }
    }
}
