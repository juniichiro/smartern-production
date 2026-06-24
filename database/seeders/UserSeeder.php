<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. SUPER ADMIN (Hidden Root - MFA Enabled)
        User::create([
            'role_id' => UserRole::SUPER_ADMIN,
            'display_id' => 'sa-' . \Illuminate\Support\Str::random(10),
            'public_id' => hash('sha256', \Illuminate\Support\Str::random(16)),
            'email' => 'root@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // 1. ADMINS (Original - Email MFA Enabled)
        User::create([
            'role_id' => UserRole::ADMIN,
            'display_id' => '2026101',
            'public_id' => hash('sha256', '2026101'),
            'email' => 'admin@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::ADMIN,
            'display_id' => '2026102',
            'public_id' => hash('sha256', '2026102'),
            'email' => 'britosranieljohn@gmail.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // 2. HEAD SUPERVISOR (Original - Email MFA Enabled)
        User::create([
            'role_id' => UserRole::HEAD_SUPERVISOR,
            'display_id' => '2026201',
            'public_id' => hash('sha256', '2026201'),
            'email' => 'malin@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // 3. SUPERVISORS (All Email MFA Enabled)
        User::create([
            'role_id' => UserRole::SUPERVISOR,
            'display_id' => '20263001',
            'public_id' => hash('sha256', '20263001'),
            'email' => 'supervisor@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SUPERVISOR,
            'display_id' => '20263002',
            'public_id' => hash('sha256', '20263002'),
            'email' => 'finance_super@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SUPERVISOR,
            'display_id' => '20263003',
            'public_id' => hash('sha256', '20263003'),
            'email' => 'admin_super@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // 4. SCHOOL COORDINATORS (All Email MFA Enabled)
        User::create([
            'role_id' => UserRole::SCHOOL_COORDINATOR,
            'display_id' => '20264001',
            'public_id' => hash('sha256', '20264001'),
            'email' => 'sagsagat@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SCHOOL_COORDINATOR,
            'display_id' => '20264002',
            'public_id' => hash('sha256', '20264002'),
            'email' => 'dlsu_coord@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SCHOOL_COORDINATOR,
            'display_id' => '20264003',
            'public_id' => hash('sha256', '20264003'),
            'email' => 'ust_coord@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SCHOOL_COORDINATOR,
            'display_id' => '20264004',
            'public_id' => hash('sha256', '20264004'),
            'email' => 'up_coord@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // 5. INTERNS (1 MFA-Enabled User Per Office)
        
        // Intern 1 (Office 1: ICT) - Email MFA Enabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650001',
            'public_id' => hash('sha256', '202650001'),
            'email' => 'raagustin@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 2 (Office 2: HR) - Email MFA Enabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650002',
            'public_id' => hash('sha256', '202650002'),
            'email' => 'rtbritos@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 3 (Office 2: HR) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650003',
            'public_id' => hash('sha256', '202650003'),
            'email' => 'tcvalera@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 4 (Office 2: HR) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650004',
            'public_id' => hash('sha256', '202650004'),
            'email' => 'hanni.phambb4@gmail.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 5 (Office 3: Finance) - Email MFA Enabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650005',
            'public_id' => hash('sha256', '202650005'),
            'email' => 'john.doe@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 6 (Office 3: Finance) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650006',
            'public_id' => hash('sha256', '202650006'),
            'email' => 'jane.smith@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 7 (Office 4: Admin) - Email MFA Enabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650007',
            'public_id' => hash('sha256', '202650007'),
            'email' => 'mark.santos@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 8 (Office 1: ICT) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650008',
            'public_id' => hash('sha256', '202650008'),
            'email' => 'sophia.lee@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 9 (Office 2: HR) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650009',
            'public_id' => hash('sha256', '202650009'),
            'email' => 'chloe.lim@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern 10 (Office 2: HR) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650010',
            'public_id' => hash('sha256', '202650010'),
            'email' => 'david.tan@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);
    }
}
