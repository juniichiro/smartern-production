<?php

namespace Database\Seeders;

use App\Models\InternDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class InternDetailSeeder extends Seeder
{
    public function run(): void
    {
        $interns = [
            [
                'email' => 'raagustin@smartern.com',
                'office_id' => 1,
                'first_name' => 'Ren Ren',
                'last_name' => 'Agustin',
                'course' => 'Information Technology',
                'school' => 'FEU-TECH',
                'gender' => 'Male',
                'phone_number' => '09123456783',
            ],
            [
                'email' => 'rtbritos@smartern.com',
                'office_id' => 2,
                'first_name' => 'Raniel John',
                'last_name' => 'Britos',
                'course' => 'Information Technology',
                'school' => 'FEU-TECH',
                'gender' => 'Male',
                'phone_number' => '09123456784',
            ],
            [
                'email' => 'tcvalera@smartern.com',
                'office_id' => 2,
                'first_name' => 'Tamiyah Gale',
                'last_name' => 'Valera',
                'course' => 'Information Technology',
                'school' => 'FEU-TECH',
                'gender' => 'Female',
                'phone_number' => '09123456785',
                'hours_rendered' => 480,
            ],
            [
                'email' => 'hanni.phambb4@gmail.com',
                'office_id' => 2,
                'first_name' => 'Hanni',
                'last_name' => 'Pham',
                'course' => 'Information Technology',
                'school' => 'FEU-TECH',
                'gender' => 'Female',
                'phone_number' => '09123456785',
                'hours_rendered' => 480,
            ],
        ];

        foreach ($interns as $data) {
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                unset($data['email']);
                $data['user_id'] = $user->id;
                InternDetail::create($data);
            }
        }
    }
}
