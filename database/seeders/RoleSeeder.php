<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin'],
            ['name' => 'head_supervisor'],
            ['name' => 'supervisor'],
            ['name' => 'school_coordinator'],
            ['name' => 'intern'],
            ['name' => 'super_admin'],
        ];

        // Using DB::table to bypass mass-assignment protection for this initial setup
        DB::table('roles')->insert($roles);
    }
}
