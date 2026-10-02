<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('settings modal component renders with all required sections and design tokens', function () {
    $user = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
        'google2fa_enabled' => true,
        'google2fa_secret' => 'GJ2TQMRWG44DQOBX',
    ]);

    $this->actingAs($user);

    $html = Blade::render('<x-settings-modal />');

    // Title and structure (sentence case, no redundant titles)
    expect($html)->toContain('Settings');
    expect($html)->toContain('General');
    expect($html)->toContain('Security & MFA');
    expect($html)->toContain('SmarTern v1.0');

    // Accessibility attributes
    expect($html)->toContain('role="dialog"');
    expect($html)->toContain('aria-modal="true"');
    expect($html)->toContain('aria-labelledby="settings-modal-title"');
    expect($html)->toContain('role="tablist"');
    expect($html)->toContain('role="tab"');
    expect($html)->toContain('role="tabpanel"');

    // Theme Options: Light, Dark, System
    expect($html)->toContain('Light');
    expect($html)->toContain('Dark');
    expect($html)->toContain('System');
    expect($html)->toContain('Clean, high-contrast light theme');
    expect($html)->toContain('Easier on the eyes in low light');
    expect($html)->toContain('Matches your device\'s appearance');

    // Text Display Scale: Small, Medium, Large, Extra large
    expect($html)->toContain('Small');
    expect($html)->toContain('Medium');
    expect($html)->toContain('Large');
    expect($html)->toContain('Extra large');
    expect($html)->toContain('14px');
    expect($html)->toContain('16px (Default)');
    expect($html)->toContain('18px');
    expect($html)->toContain('20px');

    // Live preview area
    expect($html)->toContain('Live preview');
    expect($html)->toContain('Alex Rivera');
    expect($html)->toContain('Active');
    expect($html)->toContain('View details');
    expect($html)->toContain('Edit profile');

    // Auto-save feedback & Reset
    expect($html)->toContain('Changes are saved automatically');
    expect($html)->toContain('Reset to defaults');
    expect($html)->toContain('Undo');

    // MFA Section
    expect($html)->toContain('Two-factor authentication');
    expect($html)->toContain('Authenticator application (TOTP)');
    expect($html)->toContain('Email verification (OTP)');
});

test('settings trigger is present in sidebar for all signed-in user roles', function ($portalType) {
    $user = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    $html = Blade::render('<x-sidebar :portalType="$portalType" userName="Test User" userRole="User" />', [
        'portalType' => $portalType,
    ]);

    expect($html)->toContain('system-settings-modal');
    expect($html)->toContain('Settings');
})->with([
    'Admin' => 'Admin',
    'Supervisor' => 'Supervisor',
    'Head Supervisor' => 'Head Supervisor',
    'School Coordinator' => 'School Coordinator',
    'Intern' => 'Intern',
]);

test('mfa ajax endpoints are accessible and functional for settings modal', function () {
    $user = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::INTERN,
        'google2fa_enabled' => false,
    ]);

    $this->actingAs($user);

    // 1. Get setup data
    $response = $this->getJson(route('mfa.setup-data'));
    $response->assertSuccessful();
    $response->assertJsonStructure(['secret', 'qrCodeInline']);

    // 2. Disable MFA ajax
    $responseDisable = $this->postJson(route('mfa.disable-mfa-ajax'));
    $responseDisable->assertSuccessful();
    $responseDisable->assertJson(['success' => true]);
});

test('layouts include color-scheme meta and themeMode pre-paint script', function () {
    $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
    $guestLayout = file_get_contents(resource_path('views/layouts/guest.blade.php'));

    // App layout
    expect($appLayout)->toContain('<meta name="color-scheme" content="light dark">');
    expect($appLayout)->toContain("localStorage.getItem('themeMode')");
    expect($appLayout)->toContain('window.matchMedia(\'(prefers-color-scheme: dark)\')');
    expect($appLayout)->toContain('document.documentElement.style.colorScheme');

    // Guest layout
    expect($guestLayout)->toContain('<meta name="color-scheme" content="light dark">');
    expect($guestLayout)->toContain("localStorage.getItem('themeMode')");
    expect($guestLayout)->toContain('window.matchMedia(\'(prefers-color-scheme: dark)\')');
    expect($guestLayout)->toContain('document.documentElement.style.colorScheme');
});
