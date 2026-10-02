<?php

use App\Enums\UserRole;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\SchoolCoordinatorDetail;
use App\Models\SupervisorDetail;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = User::withoutGlobalScopes()->where('email', 'test@smartern.com')->first();
        if ($user) {
            InternDetail::where('user_id', $user->id)->delete();
            SupervisorDetail::where('user_id', $user->id)->delete();
            SchoolCoordinatorDetail::where('user_id', $user->id)->delete();
            $user->delete();
        }
    }
};
