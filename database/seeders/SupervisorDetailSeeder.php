<?php

namespace Database\Seeders;

use App\Models\SupervisorDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupervisorDetailSeeder extends Seeder
{
    public function run(): void
    {
        // Role 2: Head Supervisor (Malin) -> Assigned to Human Resources
        $malin = User::where('email', 'malin@smartern.com')->first();
        if ($malin) {
            SupervisorDetail::create([
                'user_id' => $malin->id,
                'office_id' => 2, // Human Resources
                'first_name' => 'Michelle',
                'last_name' => 'Malin',
                'phone_number' => '09000000001',
                'is_hr_head' => true, // Head Supervisor logic
            ]);
        }

        // Role 3: Standard Supervisor -> Assigned to ICT Department
        $supervisor = User::where('email', 'supervisor@smartern.com')->first();
        if ($supervisor) {
            SupervisorDetail::create([
                'user_id' => $supervisor->id,
                'office_id' => 1, // ICT Department
                'first_name' => 'Sample',
                'last_name' => 'Supervisor',
                'phone_number' => '09000000002',
                'is_hr_head' => false,
            ]);
        }
    }
}
