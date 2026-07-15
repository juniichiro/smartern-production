<?php

use App\Models\InternDetail;
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

function createTestInternDetails($userId)
{
    return InternDetail::create([
        'user_id' => $userId,
        'first_name' => 'Test',
        'last_name' => 'User',
        'school' => 'Test School',
        'course' => 'ICT',
        'gender' => 'male',
        'phone_number' => '09000000000',
        'office_id' => 1,
    ]);
}

test('profile page is displayed', function () {
    $user = User::factory()->create();
    createTestInternDetails($user->id);

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();
    createTestInternDetails($user->id);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'New Name',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('New Name', $user->name);
    $this->assertSame('test@example.com', $user->email);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();
    createTestInternDetails($user->id);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('user can delete their account', function () {
    $user = User::factory()->create();
    createTestInternDetails($user->id);

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();
    createTestInternDetails($user->id);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
