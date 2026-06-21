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
                // If 0, let's add a few days for testing
                $hours = 40; 
            }

            // 8 hours per day
            $daysToSeed = ceil($hours / 8);
            $startDate = Carbon::now()->subDays($daysToSeed + 5);

            for ($i = 0; $i < $daysToSeed; $i++) {
                $currentDate = $startDate->copy()->addDays($i);

                // Skip weekends
                if ($currentDate->isWeekend()) {
                    $daysToSeed++; // Push forward to ensure we meet hours
                    continue;
                }

                // Randomize clock in/out slightly around 8:00 AM and 5:00 PM
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
            }
        }
    }
}
