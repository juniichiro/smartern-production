<?php

namespace Database\Seeders;

use App\Models\OnboardingApplicant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OnboardingApplicantSeeder extends Seeder
{
    public function run(): void
    {
        $activeAssessments = \App\Models\AssessmentItem::active()->get();

        $applicants = [
            [
                'applicant_id' => 'APP-2026-001',
                'full_name' => 'Alice Johnson',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.j@example.com',
                'university' => 'DLSU',
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
                'gender' => 'Male',
                'phone_number' => '09991234569',
                'emergency_contact' => 'Arthur Wright',
                'emergency_contact_number' => '09991234570',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-006',
                'full_name' => 'Leah Diaz',
                'first_name' => 'Leah',
                'last_name' => 'Diaz',
                'email' => 'leah.diaz@smartern.com',
                'university' => 'FEU-TECH',
                'gender' => 'Female',
                'phone_number' => '09991234571',
                'emergency_contact' => 'Isabella Diaz',
                'emergency_contact_number' => '09991234572',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-007',
                'full_name' => 'James Lim',
                'first_name' => 'James',
                'last_name' => 'Lim',
                'email' => 'james.lim@smartern.com',
                'university' => 'DLSU',
                'gender' => 'Male',
                'phone_number' => '09991234573',
                'emergency_contact' => 'Peter Lim',
                'emergency_contact_number' => '09991234574',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-008',
                'full_name' => 'Carlos Reyes',
                'first_name' => 'Carlos',
                'last_name' => 'Reyes',
                'email' => 'carlos.reyes@smartern.com',
                'university' => 'UST',
                'gender' => 'Male',
                'phone_number' => '09991234575',
                'emergency_contact' => 'Julia Reyes',
                'emergency_contact_number' => '09991234576',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
            [
                'applicant_id' => 'APP-2026-009',
                'full_name' => 'Maria Santos',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria.santos@smartern.com',
                'university' => 'UP',
                'gender' => 'Female',
                'phone_number' => '09991234577',
                'emergency_contact' => 'Juan Santos',
                'emergency_contact_number' => '09991234578',
                'privacy_acknowledged_at' => Carbon::now(),
            ],
        ];

        foreach ($applicants as $applicant) {
            $techSkills = [];
            $softSkills = [];

            foreach ($activeAssessments as $item) {
                $compText = trim($item->competency_text);
                $score = rand(3, 5);
                if ($item->category === 'technical') {
                    $techSkills[$compText] = $score;
                } else {
                    $softSkills[$compText] = $score;
                }
            }

            $applicant['tech_skills'] = $techSkills;
            $applicant['soft_skills'] = $softSkills;

            OnboardingApplicant::create($applicant);
        }

        // Auto-run the matching engine logic for seeded applicants
        \Illuminate\Support\Facades\Artisan::call('matching:run');
    }
}
