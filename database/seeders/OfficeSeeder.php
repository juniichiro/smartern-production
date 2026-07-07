<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        DB::table('offices')->insert([
            [
                'id' => 1,
                'name' => 'Information and Communication Technology (ICT) Unit',
                'location_data' => json_encode([
                    'building' => 'Main Building',
                    'floor' => '3rd Floor',
                ]),
                'latitude' => 14.61504850,
                'longitude' => 121.01059830,
                'radius_meters' => 100,
                'intern_limit' => 10,
            ],
            [
                'id' => 2,
                'name' => 'Human Resource Management Unit (HRMU)',
                'location_data' => json_encode([
                    'building' => 'Main Building',
                    'floor' => '2nd Floor',
                ]),
                'latitude' => 14.61504850,
                'longitude' => 121.01059830,
                'radius_meters' => 100,
                'intern_limit' => 10,
            ],
            [
                'id' => 3,
                'name' => 'Budget & GSIS LOAN & Remittance Units',
                'location_data' => json_encode([
                    'building' => 'Annex Building',
                    'floor' => '1st Floor',
                ]),
                'latitude' => 14.61504850,
                'longitude' => 121.01059830,
                'radius_meters' => 100,
                'intern_limit' => 10,
            ],
            [
                'id' => 4,
                'name' => 'Records Unit',
                'location_data' => json_encode([
                    'building' => 'Annex Building',
                    'floor' => '2nd Floor',
                ]),
                'latitude' => 14.61504850,
                'longitude' => 121.01059830,
                'radius_meters' => 100,
                'intern_limit' => 10,
            ],
        ]);
    }
}
