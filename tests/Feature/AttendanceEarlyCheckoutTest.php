<?php

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Vinkla\Hashids\Facades\Hashids;

uses(RefreshDatabase::class, WithoutMiddleware::class);

beforeEach(function () {
    // 0. Setup Roles (if not using seeders)
    Role::create(['id' => 1, 'name' => 'Admin']);
    Role::create(['id' => 2, 'name' => 'Head Supervisor']);
    Role::create(['id' => 3, 'name' => 'Supervisor']);
    Role::create(['id' => 4, 'name' => 'School Coordinator']);
    Role::create(['id' => 5, 'name' => 'Intern']);

    // 1. Setup Office
    $this->office = Office::create([
        'name' => 'IT Department',
        'latitude' => 14.6507,
        'longitude' => 121.0483,
        'radius_meters' => 100,
    ]);

    // 2. Setup User (rtbritos or similar)
    $this->user = User::create([
        'role_id' => UserRole::INTERN,
        'display_id' => '202650001',
        'public_id' => 'test-intern-public-id',
        'email' => 'rtbritos@example.com',
        'password' => bcrypt('password'),
    ]);

    InternDetail::create([
        'user_id' => $this->user->id,
        'office_id' => $this->office->id,
        'first_name' => 'Raniel',
        'last_name' => 'Britos',
        'course' => 'BSIT',
        'gender' => 'male',
        'phone_number' => '09123456789',
        'school' => 'Quezon City University',
    ]);
});

test('attendance flow: check-in, then early checkout with reason', function () {
    $user = $this->user;

    // Force environment to production for this test to avoid the "diff < 2" local logic
    app()->detectEnvironment(fn () => 'production');

    // --- STEP 1: Simulate Time-In ---
    $startTime = Carbon::now()->startOfDay()->addHours(8); // 8:00 AM
    Carbon::setTestNow($startTime);

    $token = Hashids::encode($user->id, $startTime->timestamp);

    $responseIn = $this->actingAs($user)
        ->postJson(route('attendance.process'), [
            'token' => $token,
            'latitude' => 14.6507,
            'longitude' => 121.0483,
        ]);

    $responseIn->assertStatus(200);

    // --- STEP 2: Simulate Early Time-Out Request (First Scan) ---
    // Move forward past the 60-min security lock
    $earlyTime = $startTime->copy()->addMinutes(65); // 9:05 AM
    Carbon::setTestNow($earlyTime);

    $tokenOut = Hashids::encode($user->id, $earlyTime->timestamp);

    $responseOutFirst = $this->actingAs($user)
        ->postJson(route('attendance.process'), [
            'token' => $tokenOut,
            'latitude' => 14.6507,
            'longitude' => 121.0483,
        ]);

    // Should return requires_confirmation because it's 9:05 AM (before 5:00 PM)
    $responseOutFirst->assertStatus(200)
        ->assertJson([
            'requires_confirmation' => true,
            'requires_reason' => true,
        ]);

    // --- STEP 3: Finalize Early Time-Out with Reason ---
    $reason = 'Finished all tasks and authorized by supervisor.';

    $responseOutFinal = $this->actingAs($user)
        ->postJson(route('attendance.process'), [
            'token' => $tokenOut,
            'latitude' => 14.6507,
            'longitude' => 121.0483,
            'confirm_early_checkout' => true,
            'early_checkout_reason' => $reason,
        ]);

    $responseOutFinal->assertStatus(200);
    expect($responseOutFinal->json('message'))->toContain('Clock-out successful');

    // --- STEP 4: Verify Database ---
    $attendance = Attendance::where('intern_id', $user->id)->first();
    expect($attendance->clock_out)->not->toBeNull();
    expect($attendance->remarks)->toContain($reason);
    expect($attendance->remarks)->toContain('Left early');

    // Clean up
    Carbon::setTestNow();
});
