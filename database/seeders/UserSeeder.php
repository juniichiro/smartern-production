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
        // Create the basic Admin Account - MFA Enabled by default, Secret generated on first login
        User::create([
            'role_id' => UserRole::ADMIN, // 'admin' role from RoleSeeder
            'display_id' => '2026101',
            'public_id' => hash('sha256', '2026101'),
            'email' => 'admin@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
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
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::HEAD_SUPERVISOR,
            'display_id' => '2026201',
            'public_id' => hash('sha256', '2026201'),
            'email' => 'malin@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SUPERVISOR,
            'display_id' => '20263001',
            'public_id' => hash('sha256', '20263001'),
            'email' => 'supervisor@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::SCHOOL_COORDINATOR,
            'display_id' => '20264001',
            'public_id' => hash('sha256', '20264001'),
            'email' => 'sagsagat@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);

        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650001',
            'public_id' => hash('sha256', '202650001'),
            'email' => 'raagustin@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);
        User::create([
            'role_id' => UserRole::INTERN,
            'display_id' => '202650002',
            'public_id' => hash('sha256', '202650002'),
            'email' => 'rtbritos@smartern.com',
            'password' => Hash::make('password'),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'failed_attempts' => 0,
            'is_first_login' => false,
        ]);
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
    }
}
