<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        // SUPER ADMIN (Hidden Root - MFA Enabled)
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

        // ADMINS (Original - Email MFA Enabled)
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
            'google2fa_enabled' => false, // MFA Disabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // HEAD SUPERVISORS (MFA mix)
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

        User::create([
            'role_id' => UserRole::HEAD_SUPERVISOR,
            'display_id' => '2026202',
            'public_id' => hash('sha256', '2026202'),
            'email' => 'assistant_hs@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false, // MFA Disabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // SUPERVISORS (MFA mix)
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
            'google2fa_enabled' => false, // MFA Disabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SUPERVISOR,
            'display_id' => '20263004',
            'public_id' => hash('sha256', '20263004'),
            'email' => 'cid_super@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => true, // Email MFA Enabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SUPERVISOR,
            'display_id' => '20263005',
            'public_id' => hash('sha256', '20263005'),
            'email' => 'sgod_super@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false, // MFA Disabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // SCHOOL COORDINATORS (MFA mix)
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
            'google2fa_enabled' => false, // MFA Disabled
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
            'google2fa_enabled' => false, // MFA Disabled
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // INTERNS (one MFA-Enabled User Per Office)
        
        // Intern one (Office ICT) - Email MFA Enabled
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

        // Intern two (Office HR) - Email MFA Enabled
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

        // Intern three (Office HR) - MFA Disabled
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

        // Intern four (Office HR) - MFA Disabled
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

        // Intern five (Office Finance) - Email MFA Enabled
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

        // Intern six (Office Finance) - MFA Disabled
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

        // Intern seven (Office Admin) - Email MFA Enabled
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

        // Intern eight (Office ICT) - MFA Disabled
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

        // Intern nine (Office HR) - MFA Disabled
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

        // Intern ten (Office HR) - MFA Disabled
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

        // Intern eleven (Office ICT) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650011',
            'public_id' => hash('sha256', '202650011'),
            'email' => 'leah.diaz@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern twelve (Office HR) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650012',
            'public_id' => hash('sha256', '202650012'),
            'email' => 'james.lim@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern thirteen (Office Finance) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650013',
            'public_id' => hash('sha256', '202650013'),
            'email' => 'carlos.reyes@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern fourteen (Office Admin) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650014',
            'public_id' => hash('sha256', '202650014'),
            'email' => 'maria.santos@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern fifteen (Office ICT) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650015',
            'public_id' => hash('sha256', '202650015'),
            'email' => 'alex.tan@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern sixteen (Office Finance) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650016',
            'public_id' => hash('sha256', '202650016'),
            'email' => 'bea.gomez@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern seventeen (Office HR) - MFA Disabled
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650017',
            'public_id' => hash('sha256', '202650017'),
            'email' => 'cris.valdez@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        // Intern eighteen to twenty-seven - MFA Disabled
        for ($i = 18; $i <= 27; $i++) {
            $displayId = '2026500' . $i;
            User::create([
                'role_id' => UserRole::INTERN,
                'display_id' => $displayId,
                'public_id' => hash('sha256', $displayId),
                'email' => 'intern' . $i . '@smartern.com',
                'password' => Hash::make('password'),
                'google2fa_secret' => null,
                'google2fa_enabled' => false,
                'failed_attempts' => 0,
                'is_first_login' => false,
            ]);
        }
    }
}
