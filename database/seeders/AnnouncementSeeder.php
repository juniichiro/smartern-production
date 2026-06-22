<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\SupervisorDetail;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $supervisors = SupervisorDetail::with('user')->get();

        foreach ($supervisors as $supervisor) {
            $officeName = $supervisor->office->name ?? 'Office';

            // Post two announcements per supervisor (thus per office)
            Announcement::create([
                'user_id' => $supervisor->user_id,
                'title' => "Welcome to {$officeName} Department!",
                'content' => "Hello interns! We are excited to have you in the {$officeName} department. Please make sure to read the guidelines and complete your daily tasks on time. Reach out if you have any questions.",
            ]);

            Announcement::create([
                'user_id' => $supervisor->user_id,
                'title' => "Weekly Progress Report Reminder",
                'content' => "This is a reminder to submit your weekly progress journal and task completion updates every Friday by 5:00 PM. Late submissions may affect your evaluation.",
            ]);
        }
    }
}
