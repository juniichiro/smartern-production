<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\SchoolCoordinatorDetail;
use App\Models\SupervisorDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::withoutGlobalScopes()->where('email', 'test@smartern.com')->first();

        if (! $user) {
            $user = User::create([
                'role_id' => UserRole::SUPER_ADMIN,
                'display_id' => 'TEST001',
                'public_id' => hash('sha256', 'TEST001'),
                'email' => 'test@smartern.com',
                'password' => Hash::make('password'),
                'google2fa_secret' => null,
                'google2fa_enabled' => false,
                'failed_attempts' => 0,
                'is_first_login' => false,
                'privacy_acknowledged_at' => now(),
            ]);
        }

        $defaultOfficeId = Office::first()?->id ?? 1;

        // Ensure SupervisorDetail exists (with is_hr_head = true to support both Supervisor and Head Supervisor views)
        if (! SupervisorDetail::where('user_id', $user->id)->exists()) {
            SupervisorDetail::create([
                'user_id' => $user->id,
                'office_id' => $defaultOfficeId,
                'first_name' => 'Test',
                'last_name' => 'SuperAdmin',
                'phone_number' => '09000000000',
                'is_hr_head' => true,
            ]);
        }

        // Ensure InternDetail exists (so Intern views load all relationships without error)
        if (! InternDetail::where('user_id', $user->id)->exists()) {
            InternDetail::create([
                'user_id' => $user->id,
                'office_id' => $defaultOfficeId,
                'first_name' => 'Test',
                'last_name' => 'SuperAdmin',
                'course' => 'BS Information Technology',
                'school' => 'FEU-TECH',
                'gender' => 'other',
                'phone_number' => '09000000000',
                'hours_rendered' => 120.0,
                'required_hours' => 480,
                'start_date' => now()->startOfYear()->toDateString(),
                'end_date' => now()->endOfYear()->toDateString(),
            ]);
        }

        // Ensure SchoolCoordinatorDetail exists (so School Coordinator views load smoothly)
        if (! SchoolCoordinatorDetail::where('user_id', $user->id)->exists()) {
            SchoolCoordinatorDetail::create([
                'user_id' => $user->id,
                'first_name' => 'Test',
                'last_name' => 'SuperAdmin',
                'school' => 'FEU-TECH',
                'phone_number' => '09000000000',
            ]);
        }
    }
}
