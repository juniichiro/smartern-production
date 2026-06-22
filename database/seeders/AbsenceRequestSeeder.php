<?php

namespace Database\Seeders;

use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\SupervisorDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AbsenceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $interns = InternDetail::all();
        $supervisor = SupervisorDetail::first(); // Grab any supervisor to review

        if (!$supervisor) {
            return;
        }

        foreach ($interns as $intern) {
            $userId = $intern->user_id;

            // 1. Approved Absence (Date in the past)
            $pastDate1 = Carbon::now()->subDays(10)->format('Y-m-d');
            // Delete any existing attendance on this day to simulate real absence
            Attendance::where('intern_id', $userId)->whereDate('clock_in', $pastDate1)->delete();

            AbsenceRequest::create([
                'intern_id' => $userId,
                'date' => $pastDate1,
                'type' => 'absence',
                'reason' => 'Medical appointment and lab tests.',
                'status' => 'approved',
                'supervisor_remarks' => 'Approved. Please submit your medical certificate.',
                'reviewed_by' => $supervisor->user_id,
                'reviewed_at' => Carbon::now()->subDays(10),
            ]);

            // 2. Approved Early Leave (Date in the past)
            $pastDate2 = Carbon::now()->subDays(6)->format('Y-m-d');
            
            // Adjust attendance for early leave (clock out at 12:00 PM instead of 5:00 PM)
            $attendance = Attendance::where('intern_id', $userId)->whereDate('clock_in', $pastDate2)->first();
            if ($attendance) {
                $attendance->update([
                    'clock_out' => Carbon::parse($pastDate2 . ' 12:00:00'),
                    'remarks' => 'Left early (Approved Early Leave Request).',
                ]);
            } else {
                Attendance::create([
                    'intern_id' => $userId,
                    'office_id' => $intern->office_id,
                    'clock_in' => Carbon::parse($pastDate2 . ' 08:00:00'),
                    'clock_out' => Carbon::parse($pastDate2 . ' 12:00:00'),
                    'status' => 'on_time',
                    'remarks' => 'Left early (Approved Early Leave Request).',
                ]);
            }

            AbsenceRequest::create([
                'intern_id' => $userId,
                'date' => $pastDate2,
                'type' => 'early_leave',
                'reason' => 'Family emergency. Need to attend to an urgent matter at home.',
                'status' => 'approved',
                'supervisor_remarks' => 'Approved. Keep safe.',
                'reviewed_by' => $supervisor->user_id,
                'reviewed_at' => Carbon::now()->subDays(6),
            ]);

            // 3. Pending Request (Date in the future)
            $futureDate = Carbon::now()->addDays(3)->format('Y-m-d');
            
            // Delete any unique conflicts just in case
            AbsenceRequest::where('intern_id', $userId)->where('date', $futureDate)->delete();

            AbsenceRequest::create([
                'intern_id' => $userId,
                'date' => $futureDate,
                'type' => 'absence',
                'reason' => 'School seminar and graduation preparation duties.',
                'status' => 'pending',
            ]);
        }
    }
}
