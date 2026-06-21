<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('offices')->insert([
            [
                'id' => 1,
                'name' => 'ICT Department',
                'location_data' => json_encode([
                    'building' => 'Main Building',
                    'floor' => '3rd Floor',
                ]),
            ],
            [
                'id' => 2, // This matches Michelle's office_id from the payload
                'name' => 'Human Resources',
                'location_data' => json_encode([
                    'building' => 'Main Building',
                    'floor' => '2nd Floor',
                ]),
            ],
            [
                'id' => 3,
                'name' => 'Finance & Accounting',
                'location_data' => json_encode([
                    'building' => 'Annex Building',
                    'floor' => '1st Floor',
                ]),
            ],
            [
                'id' => 4,
                'name' => 'Administrative Services',
                'location_data' => json_encode([
                    'building' => 'Annex Building',
                    'floor' => '2nd Floor',
                ]),
            ],
        ]);
    }
}
