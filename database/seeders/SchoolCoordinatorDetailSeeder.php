<?php

namespace Database\Seeders;

use App\Models\SchoolCoordinatorDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolCoordinatorDetailSeeder extends Seeder
{
    public function run(): void
    {
        // Role 4: Coordinator (Sagsagat)
        $coordinator = User::where('email', 'sagsagat@smartern.com')->first();
        if ($coordinator) {
            SchoolCoordinatorDetail::create([
                'user_id' => $coordinator->id,
                'first_name' => 'Mar Eli',
                'last_name' => 'Sagsagat',
                'school' => 'FEU-TECH',
                'phone_number' => '09000000003',
            ]);
        }

        $dlsu = User::where('email', 'dlsu_coord@smartern.com')->first();
        if ($dlsu) {
            SchoolCoordinatorDetail::create([
                'user_id' => $dlsu->id,
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'school' => 'DLSU',
                'phone_number' => '09000000006',
            ]);
        }

        $ust = User::where('email', 'ust_coord@smartern.com')->first();
        if ($ust) {
            SchoolCoordinatorDetail::create([
                'user_id' => $ust->id,
                'first_name' => 'Dominic',
                'last_name' => 'de Guzman',
                'school' => 'UST',
                'phone_number' => '09000000007',
            ]);
        }

        $up = User::where('email', 'up_coord@smartern.com')->first();
        if ($up) {
            SchoolCoordinatorDetail::create([
                'user_id' => $up->id,
                'first_name' => 'Fidel',
                'last_name' => 'Ramos',
                'school' => 'UP',
                'phone_number' => '09000000008',
            ]);
        }
    }
}
