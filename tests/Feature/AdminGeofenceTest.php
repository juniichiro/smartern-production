<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Office;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Cache::flush();
});

test('admin can access the geofence overview page', function () {
    $admin = User::factory()->create(['role_id' => UserRole::ADMIN]);
    $office = Office::create([
        'name' => 'ICT Unit',
        'latitude' => 14.6150485,
        'longitude' => 121.0105983,
        'radius_meters' => 100,
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->get(route('admin.offices'));

    $response->assertOk();
    $response->assertViewIs('admin.offices');
    $response->assertSee('Geofence Overview');
    $response->assertSee('ICT Unit');
});

test('non-admin is forbidden from accessing the geofence overview page', function () {
    $intern = User::factory()->create(['role_id' => UserRole::INTERN]);

    $response = $this->actingAs($intern)
        ->withSession(['mfa_verified' => true])
        ->get(route('admin.offices'));

    $response->assertForbidden();
});

test('admin can reverse geocode coordinates through server endpoint', function () {
    $admin = User::factory()->create(['role_id' => UserRole::ADMIN]);

    Http::fake([
        'https://nominatim.openstreetmap.org/reverse*' => Http::response([
            'display_name' => 'Department of Education, Pasig City',
        ], 200),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->getJson(route('admin.offices.geofence.reverse', [
            'latitude' => 14.61505,
            'longitude' => 121.01060,
        ]));

    $response->assertOk();
    $response->assertJson([
        'address' => 'Department of Education, Pasig City',
        'latitude' => 14.61505,
        'longitude' => 121.0106,
    ]);

    // Verify result was stored in cache
    expect(Cache::has('geocode_rev_14.61505_121.01060'))->toBeTrue();
});

test('cached reverse geocoding returns without external http request', function () {
    $admin = User::factory()->create(['role_id' => UserRole::ADMIN]);

    Cache::put('geocode_rev_14.61505_121.01060', 'Cached Pasig Office', now()->addDays(30));

    Http::fake(); // No requests should be recorded

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->getJson(route('admin.offices.geofence.reverse', [
            'latitude' => 14.61505,
            'longitude' => 121.01060,
        ]));

    $response->assertOk();
    $response->assertJson([
        'address' => 'Cached Pasig Office',
    ]);

    Http::assertNothingSent();
});

test('reverse geocoding returns null address gracefully on service failure', function () {
    $admin = User::factory()->create(['role_id' => UserRole::ADMIN]);

    Http::fake([
        'https://nominatim.openstreetmap.org/reverse*' => Http::response(null, 500),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->getJson(route('admin.offices.geofence.reverse', [
            'latitude' => 14.61505,
            'longitude' => 121.01060,
        ]));

    $response->assertOk();
    $response->assertJson([
        'address' => null,
    ]);
});

test('unauthenticated request to reverse geocoding endpoint is blocked', function () {
    $response = $this->getJson(route('admin.offices.geofence.reverse', [
        'latitude' => 14.61505,
        'longitude' => 121.01060,
    ]));

    $response->assertUnauthorized();
});

test('admin can update geofence for a specific office with valid input', function () {
    $admin = User::factory()->create(['role_id' => UserRole::ADMIN]);
    $office = Office::create([
        'name' => 'HR Unit',
        'latitude' => 14.6100,
        'longitude' => 121.0100,
        'radius_meters' => 100,
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->put(route('admin.offices.geofence.update'), [
            'office_id' => $office->id,
            'latitude' => 14.6200,
            'longitude' => 121.0200,
            'radius_meters' => 250,
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $office->refresh();
    expect((float) $office->latitude)->toEqual(14.6200);
    expect((float) $office->longitude)->toEqual(121.0200);
    expect($office->radius_meters)->toEqual(250);

    // Verify audit log
    $log = AuditLog::where('action', 'Geofence Updated')->latest('timestamp')->first();
    expect($log)->not->toBeNull();
    expect($log->resource)->toContain("Office 'HR Unit' Radius: 250m");
});

test('admin can update global geofence across all offices', function () {
    $admin = User::factory()->create(['role_id' => UserRole::ADMIN]);
    $office1 = Office::create(['name' => 'Office 1', 'latitude' => 14.0, 'longitude' => 121.0, 'radius_meters' => 100]);
    $office2 = Office::create(['name' => 'Office 2', 'latitude' => 14.0, 'longitude' => 121.0, 'radius_meters' => 100]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->put(route('admin.offices.geofence.update'), [
            'office_id' => null,
            'latitude' => 14.6500,
            'longitude' => 121.0500,
            'radius_meters' => 300,
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    expect((float) $office1->fresh()->latitude)->toEqual(14.6500);
    expect((float) $office2->fresh()->latitude)->toEqual(14.6500);
    expect($office1->fresh()->radius_meters)->toEqual(300);
    expect($office2->fresh()->radius_meters)->toEqual(300);
});

test('validation rejects invalid radius and coordinates', function () {
    $admin = User::factory()->create(['role_id' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->put(route('admin.offices.geofence.update'), [
            'latitude' => 95.0, // Invalid lat (>90)
            'longitude' => 200.0, // Invalid lng (>180)
            'radius_meters' => 5, // Invalid radius (<10)
        ]);

    $response->assertSessionHasErrors(['latitude', 'longitude', 'radius_meters']);
});
