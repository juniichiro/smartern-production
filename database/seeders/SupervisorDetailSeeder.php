<?php

namespace Database\Seeders;

use App\Models\SupervisorDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupervisorDetailSeeder extends Seeder
{
    public function run(): void
    {
        // Head Supervisor role: Head Supervisor (Malin) -> Assigned to Human Resources
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

        // Supervisor role: Standard Supervisor -> Assigned to ICT Department
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

        $finance_super = User::where('email', 'finance_super@smartern.com')->first();
        if ($finance_super) {
            SupervisorDetail::create([
                'user_id' => $finance_super->id,
                'office_id' => 3, // Finance & Accounting
                'first_name' => 'Albert',
                'last_name' => 'Finney',
                'phone_number' => '09000000004',
                'is_hr_head' => false,
            ]);
        }

        $admin_super = User::where('email', 'admin_super@smartern.com')->first();
        if ($admin_super) {
            SupervisorDetail::create([
                'user_id' => $admin_super->id,
                'office_id' => 4, // Administrative Services
                'first_name' => 'Sarah',
                'last_name' => 'Admin',
                'phone_number' => '09000000005',
                'is_hr_head' => false,
            ]);
        }
    }
}
