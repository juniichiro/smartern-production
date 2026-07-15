<?php

use App\Models\Office;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    Role::firstOrCreate(['id' => 5], ['name' => 'Intern']);
    Office::firstOrCreate(['id' => 1], [
        'name' => 'ICT',
        'latitude' => 14.65,
        'longitude' => 121.05,
        'radius_meters' => 100,
    ]);
});

test('privacy policy page is accessible by guests', function () {
    $response = $this->get('/privacy-policy');
    $response->assertOk();
    $response->assertSee('Privacy');
    $response->assertSee('Data Privacy Statement');
    $response->assertSee('1. What is SmarTern?');
});

test('authenticated user can acknowledge privacy policy', function () {
    $user = User::factory()->create([
        'privacy_acknowledged_at' => null,
    ]);

    // Mock MFA session verification to bypass MFACheck middleware
    $response = $this->actingAs($user)
        ->withSession(['mfa_verified' => true])
        ->post('/privacy/acknowledge');

    $response->assertOk()
        ->assertJson(['success' => true]);

    $user->refresh();
    expect($user->privacy_acknowledged_at)->not->toBeNull();
});
