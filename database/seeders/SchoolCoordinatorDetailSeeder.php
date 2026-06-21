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
    }
}
