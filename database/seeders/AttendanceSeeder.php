<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\InternDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $interns = InternDetail::all();

        foreach ($interns as $intern) {
            $hours = $intern->hours_rendered ?: 0;
            if ($hours === 0) {
                // If zero, let's add a few days for testing
                $hours = 40; 
            }

            // hours per day
            $daysToSeed = ceil($hours / 8);
            
            $currentDate = Carbon::now()->subDays(2); // Generate backwards starting from 2 days ago
            $seededCount = 0;

            while ($seededCount < $daysToSeed) {
                // Skip weekends
                if ($currentDate->isWeekend()) {
                    $currentDate->subDay();
                    continue;
                }

                // Randomize clock in/out slightly around standard hours
                $clockIn = $currentDate->copy()->hour(8)->minute(rand(0, 15))->second(rand(0, 59));
                $clockOut = $currentDate->copy()->hour(17)->minute(rand(0, 10))->second(rand(0, 59));

                Attendance::create([
                    'intern_id' => $intern->user_id,
                    'office_id' => $intern->office_id,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'latitude' => 14.5995,  // Sample coords (Manila area)
                    'longitude' => 120.9842,
                    'status' => 'on_time',
                    'remarks' => 'Completed 8 hours render.',
                ]);

                $seededCount++;
                $currentDate->subDay();
            }
        }
    }
}
