<?php

namespace App\Console\Commands;

use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SimulateAttendance extends Command
{
    protected $signature = 'app:simulate-attendance';

    protected $description = 'Simulate a full week of attendance plus absence requests for intern rtbritos';

    public function handle()
    {
        $email = 'rtbritos@smartern.com';
        $this->info("🚀 Starting Advanced Attendance Simulation for {$email}...");

        // 1. Find existing User
        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User {$email} not found. Please run 'php artisan db:seed' first.");

            return;
        }

        // 2. Retroactive fix for created_at
        $accountCreationDate = Carbon::parse('2026-06-01');
        $user->created_at = $accountCreationDate;
        $user->save();

        // 3. Reset existing data for clean simulation
        Attendance::where('intern_id', $user->id)->delete();
        AbsenceRequest::where('intern_id', $user->id)->delete();
        $user->internDetails->update(['hours_rendered' => 0]);

        // Prepare storage
        if (! Storage::disk('public')->exists('absences')) {
            Storage::disk('public')->makeDirectory('absences');
        }

        // Copy a sample file for simulation if it exists
        $sampleSource = public_path('tasks/SmarTern_Office_Report.pdf');
        $simulatedPath = 'absences/simulated_attachment.pdf';
        if (File::exists($sampleSource)) {
            File::copy($sampleSource, Storage::disk('public')->path($simulatedPath));
            $this->info('INFO: Sample attachment prepared.');
        } else {
            $this->warn("WARN: Sample file not found at {$sampleSource}. Simulation will run without file content.");
        }

        $userId = $user->id;
        $officeId = $user->internDetails->office_id;

        // Reference Week (June 8 to June 12, 2026)
        $monday = Carbon::parse('2026-06-08');

        $scenarios = [
            [
                'date' => $monday->copy(), // Monday
                'label' => 'Standard Shift',
                'in' => '08:00:00',
                'out' => '17:00:00',
                'status' => 'on_time',
                'type' => 'actual',
            ],
            [
                'date' => $monday->copy()->addDay(), // Tuesday
                'label' => 'Late Check-In',
                'in' => '09:30:00',
                'out' => '17:00:00',
                'status' => 'late',
                'type' => 'actual',
            ],
            [
                'date' => $monday->copy()->addDays(2), // Wednesday
                'label' => 'Excused Absence',
                'type' => 'excused',
                'reason' => 'Family Emergency',
                'supervisor_remarks' => 'Excused. Please prioritize family.',
                'attachment' => $simulatedPath,
            ],
            [
                'date' => $monday->copy()->addDays(3), // Thursday
                'label' => 'Early Checkout',
                'in' => '08:00:00',
                'out' => '12:00:00',
                'status' => 'on_time',
                'type' => 'actual',
                'reason' => 'University Workshop',
            ],
            [
                'date' => $monday->copy()->addDays(4), // Friday
                'label' => 'Standard Shift',
                'in' => '08:00:00',
                'out' => '17:00:00',
                'status' => 'on_time',
                'type' => 'actual',
            ],
        ];

        $totalSimulatedHours = 0;

        foreach ($scenarios as $s) {
            if ($s['type'] === 'excused') {
                // 1. Create the Approved Absence Request in Schedule
                AbsenceRequest::create([
                    'intern_id' => $userId,
                    'date' => $s['date'],
                    'reason' => $s['reason'],
                    'attachment_path' => $s['attachment'] ?? null,
                    'status' => 'approved',
                    'supervisor_remarks' => $s['supervisor_remarks'],
                    'reviewed_by' => 3,
                    'reviewed_at' => now(),
                ]);

                // 2. Create the Attendance Log entry with 'excused' status
                Attendance::create([
                    'intern_id' => $userId,
                    'office_id' => $officeId,
                    'clock_in' => $s['date']->copy()->setTime(8, 0),
                    'clock_out' => $s['date']->copy()->setTime(17, 0),
                    'status' => 'excused',
                    'remarks' => "EXCUSED ABSENCE: {$s['reason']} | {$s['supervisor_remarks']}",
                ]);

                $this->info("CREATED: Approved Absence (with File) for {$s['date']->toDateString()}");

                continue;
            }

            $clockIn = $s['date']->copy()->setTimeFromTimeString($s['in']);
            $clockOut = $s['date']->copy()->setTimeFromTimeString($s['out']);

            // Calculate Rendered Hours
            $diffInMinutes = $clockIn->diffInMinutes($clockOut);
            $hours = $diffInMinutes / 60;
            if ($hours > 5.0) {
                $hours -= 1.0;
            }
            $hours = (int) round($hours);
            $totalSimulatedHours += $hours;

            // Generate Remarks
            if ($s['status'] === 'late') {
                $remarks = "Late clock-in (Scheduled: 08:00:00) | Completed shift: Rendered {$hours} hrs";
            } elseif (isset($s['reason'])) {
                $remarks = "On-time check-in | Left early ({$s['reason']}): Rendered {$hours} hrs";
            } else {
                $remarks = "On-time check-in | Completed full shift ({$hours} hrs)";
            }

            Attendance::create([
                'intern_id' => $userId,
                'office_id' => $officeId,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'latitude' => 14.6507,
                'longitude' => 121.0483,
                'status' => $s['status'],
                'remarks' => $remarks,
                'created_at' => $clockIn,
                'updated_at' => $clockOut,
            ]);

            $user->internDetails->increment('hours_rendered', $hours);
            $this->info("CREATED: {$s['label']} on {$s['date']->toDateString()} (+{$hours} hrs)");
        }

        // 4. Create a Pending Future Request (Next Monday) with Attachment
        $futureDate = Carbon::parse('2026-06-15');
        AbsenceRequest::create([
            'intern_id' => $userId,
            'date' => $futureDate,
            'reason' => 'Mandatory Thesis Defense at University',
            'attachment_path' => $simulatedPath,
            'status' => 'pending',
        ]);
        $this->info("CREATED: Pending Absence (with File) for {$futureDate->toDateString()}");

        $this->info("\n✅ Simulation Complete.");
        $this->info("Total Hours Rendered: {$totalSimulatedHours} hrs");
        $this->info("Verify 'Pending' on June 15 and 'Approved' on June 10 in the Schedule.");
    }
}
