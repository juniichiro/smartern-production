<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schools = ['FEU-TECH', 'DLSU', 'UST', 'UP'];

        foreach ($schools as $school) {
            // Default School Schedule
            Schedule::create([
                'school_name' => $school,
                'type' => 'school_default',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'daily_hours' => 8,
                'rounding_mode' => 'none',
                'description' => "Default work schedule for {$school} interns.",
            ]);

            // Seed a holiday/suspension in the past
            Schedule::create([
                'school_name' => $school,
                'type' => 'holiday',
                'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'description' => 'National Holiday - No Duty',
            ]);

            // Seed a suspension in the future
            Schedule::create([
                'school_name' => $school,
                'type' => 'suspension',
                'date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'description' => 'Inclement Weather Suspension',
            ]);
        }
    }
}
