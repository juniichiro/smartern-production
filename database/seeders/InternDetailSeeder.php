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
                'required_hours' => 200,
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
                'required_hours' => 150,
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
                'required_hours' => 480,
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
                'required_hours' => 480,
            ],
            [
                'email' => 'john.doe@smartern.com',
                'office_id' => 3, // Finance
                'first_name' => 'John',
                'last_name' => 'Doe',
                'course' => 'Business Administration',
                'school' => 'DLSU',
                'gender' => 'Male',
                'phone_number' => '09123456786',
                'hours_rendered' => 120,
                'required_hours' => 200,
            ],
            [
                'email' => 'jane.smith@smartern.com',
                'office_id' => 3, // Finance
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'course' => 'Accountancy',
                'school' => 'UST',
                'gender' => 'Female',
                'phone_number' => '09123456787',
                'hours_rendered' => 240,
                'required_hours' => 300,
            ],
            [
                'email' => 'mark.santos@smartern.com',
                'office_id' => 4, // Admin
                'first_name' => 'Mark',
                'last_name' => 'Santos',
                'course' => 'Public Administration',
                'school' => 'UP',
                'gender' => 'Male',
                'phone_number' => '09123456788',
                'hours_rendered' => 80,
                'required_hours' => 240,
            ],
            [
                'email' => 'sophia.lee@smartern.com',
                'office_id' => 1, // ICT
                'first_name' => 'Sophia',
                'last_name' => 'Lee',
                'course' => 'Computer Science',
                'school' => 'DLSU',
                'gender' => 'Female',
                'phone_number' => '09123456789',
                'hours_rendered' => 400,
                'required_hours' => 400,
            ],
            [
                'email' => 'chloe.lim@smartern.com',
                'office_id' => 2, // HR (Filling to 5 capacity)
                'first_name' => 'Chloe',
                'last_name' => 'Lim',
                'course' => 'Psychology',
                'school' => 'UST',
                'gender' => 'Female',
                'phone_number' => '09123456790',
                'hours_rendered' => 150,
                'required_hours' => 300,
            ],
            [
                'email' => 'david.tan@smartern.com',
                'office_id' => 2, // HR (Filling to 5 capacity)
                'first_name' => 'David',
                'last_name' => 'Tan',
                'course' => 'Human Resource Management',
                'school' => 'UP',
                'gender' => 'Male',
                'phone_number' => '09123456791',
                'hours_rendered' => 200,
                'required_hours' => 250,
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
