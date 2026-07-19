<?php

use App\Models\AuditLog;
use App\Models\MatchResult;
use App\Models\Office;
use App\Models\OnboardingApplicant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->seed(RoleSeeder::class);
});

test('head supervisor can undo a finalized matching placement', function () {
    // 1. Create Head Supervisor
    $headSupervisor = User::factory()->create(['role_id' => \App\Enums\UserRole::HEAD_SUPERVISOR]);

    // 2. Create Office
    $office = Office::create([
        'name' => 'Department of Science and Technology',
        'latitude' => 14.65,
        'longitude' => 121.05,
        'radius_meters' => 100,
    ]);

    // 3. Create Onboarding Applicant
    $applicant = OnboardingApplicant::create([
        'applicant_id' => 'APP-2026-0001',
        'full_name' => 'Alexander Hamilton',
        'first_name' => 'Alexander',
        'last_name' => 'Hamilton',
        'email' => 'alexander.hamilton@example.com',
        'university' => 'Columbia University',
        'tech_skills' => ['PHP', 'Laravel'],
        'soft_skills' => ['Leadership'],
        'gender' => 'male',
        'phone_number' => '09171234567',
        'emergency_contact' => 'Elizabeth Schuyler',
        'emergency_contact_number' => '09177654321',
    ]);

    // 4. Create confirmed match result
    $matchResult = MatchResult::create([
        'applicant_name' => 'Alexander Hamilton',
        'school' => 'Columbia University',
        'office_id' => $office->id,
        'score' => 95,
        'status' => 'Pending',
        'is_confirmed' => true,
    ]);

    // 5. Call unfinalize route
    $response = $this->actingAs($headSupervisor)
        ->withSession(['mfa_verified' => true])
        ->post(route('head-supervisor.matching.unfinalize'), [
            'applicant_name' => 'Alexander Hamilton',
        ]);

    // 6. Assert success redirect
    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    // 7. Verify match result is unconfirmed
    expect($matchResult->fresh()->is_confirmed)->toBeFalse();

    // 8. Verify Audit Log was created with limited/correct length content
    $auditLog = AuditLog::where('action', 'Match Unfinalized')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->resource)->toBe('Undid office placement for Alexander Hamilton from Department of Science and Technology');

    // 9. Verify Mail was queued
    Mail::assertQueued(\App\Mail\OfficeAssignmentUnfinalizedMail::class, function ($mail) use ($applicant) {
        return $mail->hasTo($applicant->email) && $mail->applicant->id === $applicant->id;
    });
});
