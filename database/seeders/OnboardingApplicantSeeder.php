<?php

namespace Database\Seeders;

use App\Models\OnboardingApplicant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OnboardingApplicantSeeder extends Seeder
{
    public function run(): void
    {
        $applicants = [
            [
                'applicant_id' => 'APP-2026-001',
                'full_name' => 'Alice Johnson',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.j@example.com',
                'university' => 'DLSU',
                'tech_skills' => ['PHP', 'Laravel', 'HTML', 'CSS'],
                'soft_skills' => ['Teamwork', 'Communication', 'Problem Solving'],
                'gender' => 'Female',
                'phone_number' => '09991234561',
                'emergency_contact' => 'Robert Johnson',
                'emergency_contact_number' => '09991234562',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-002',
                'full_name' => 'Bob Smith',
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'email' => 'bob.s@example.com',
                'university' => 'UST',
                'tech_skills' => ['Python', 'SQL', 'Data Analytics'],
                'soft_skills' => ['Critical Thinking', 'Adaptability'],
                'gender' => 'Male',
                'phone_number' => '09991234563',
                'emergency_contact' => 'Mary Smith',
                'emergency_contact_number' => '09991234564',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-003',
                'full_name' => 'Charlie Brown',
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'email' => 'charlie.b@example.com',
                'university' => 'UP',
                'tech_skills' => ['Java', 'Spring Boot', 'Git'],
                'soft_skills' => ['Leadership', 'Time Management'],
                'gender' => 'Male',
                'phone_number' => '09991234565',
                'emergency_contact' => 'Sally Brown',
                'emergency_contact_number' => '09991234566',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-004',
                'full_name' => 'Diana Prince',
                'first_name' => 'Diana',
                'last_name' => 'Prince',
                'email' => 'diana.p@example.com',
                'university' => 'FEU-TECH',
                'tech_skills' => ['JavaScript', 'React', 'Tailwind'],
                'soft_skills' => ['Creativity', 'Conflict Resolution'],
                'gender' => 'Female',
                'phone_number' => '09991234567',
                'emergency_contact' => 'Hippolyta Prince',
                'emergency_contact_number' => '09991234568',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-005',
                'full_name' => 'Evan Wright',
                'first_name' => 'Evan',
                'last_name' => 'Wright',
                'email' => 'evan.w@example.com',
                'university' => 'DLSU',
                'tech_skills' => ['UI/UX Design', 'Figma', 'Product Management'],
                'soft_skills' => ['Empathy', 'Collaboration'],
                'gender' => 'Male',
                'phone_number' => '09991234569',
                'emergency_contact' => 'Arthur Wright',
                'emergency_contact_number' => '09991234570',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
        ];

        foreach ($applicants as $applicant) {
            OnboardingApplicant::create($applicant);
        }
    }
}
