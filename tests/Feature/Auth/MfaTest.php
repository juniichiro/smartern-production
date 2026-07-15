<?php

use App\Mail\MfaEmailCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

beforeEach(function () {
    Mail::fake();
});

test('mfa verify screen can be rendered', function () {
    $user = User::factory()->create();

    // 1. By default, user with no TOTP secret gets Email verification screen
    $response1 = $this->actingAs($user)->get(route('mfa.verify'));
    $response1->assertSuccessful();
    $response1->assertViewIs('auth.mfa_verify_email');

    // 2. User with TOTP secret gets TOTP verification screen by default
    $user->google2fa_secret = 'GJ2TQMRWG44DQOBX';
    $user->save();

    $response2 = $this->actingAs($user)->get(route('mfa.verify'));
    $response2->assertSuccessful();
    $response2->assertViewIs('auth.mfa_verify_totp');

    // 3. User with TOTP secret can request Email verification screen via query parameter
    $response3 = $this->actingAs($user)->get(route('mfa.verify', ['method' => 'email']));
    $response3->assertSuccessful();
    $response3->assertViewIs('auth.mfa_verify_email');
});

test('mfa email verify view receives cooldown variable', function () {
    $user = User::factory()->create();

    // No code sent yet
    $response = $this->actingAs($user)->get(route('mfa.verify', ['method' => 'email']));
    $response->assertSuccessful();
    $response->assertViewHas('cooldown', 0);

    // Send code
    $this->actingAs($user)->postJson(route('mfa.send-email-code'));

    // Check verify page again
    $response = $this->actingAs($user)->get(route('mfa.verify', ['method' => 'email']));
    $response->assertSuccessful();
    $cooldown = $response->viewData('cooldown');
    expect($cooldown)->toBeGreaterThan(0);
    expect($cooldown)->toBeLessThanOrEqual(60);
});

test('user can request a verification code via email', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('mfa.send-email-code'));

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'Verification code has been sent to your email.',
    ]);

    // Assert mail was sent
    Mail::assertSent(MfaEmailCodeMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    // Assert code is in cache
    $cachedCode = Cache::get('mfa_email_code_'.$user->id);
    expect($cachedCode)->not->toBeNull();
    expect(strlen($cachedCode))->toBe(6);
});

test('email code request is rate limited to 60 seconds', function () {
    $user = User::factory()->create();

    // First request
    $response1 = $this->actingAs($user)->postJson(route('mfa.send-email-code'));
    $response1->assertSuccessful();

    // Second request immediately after
    $response2 = $this->actingAs($user)->postJson(route('mfa.send-email-code'));
    $response2->assertStatus(429);
    $response2->assertJson([
        'success' => false,
    ]);
});

test('user can authenticate using correct email code', function () {
    $user = User::factory()->create();

    // Request code
    $this->actingAs($user)->postJson(route('mfa.send-email-code'));
    $code = Cache::get('mfa_email_code_'.$user->id);

    $response = $this->actingAs($user)->post(route('mfa.verify.post'), [
        'one_time_password' => $code,
    ]);

    $response->assertRedirect(route('dashboard'));
    expect(session('mfa_verified'))->toBeTrue();
    expect(Cache::get('mfa_email_code_'.$user->id))->toBeNull();
});

test('user cannot authenticate using incorrect email code', function () {
    $user = User::factory()->create();

    // Request code
    $this->actingAs($user)->postJson(route('mfa.send-email-code'));

    $response = $this->actingAs($user)->post(route('mfa.verify.post'), [
        'one_time_password' => '000000', // incorrect code
    ]);

    $response->assertSessionHasErrors('one_time_password');
    expect(session('mfa_verified'))->toBeNull();
});

test('user can view the mfa setup page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('mfa.setup'));

    $response->assertSuccessful();
    $response->assertViewIs('profile.mfa_setup');
    $response->assertViewHasAll(['qrCodeUrl', 'secret', 'enabled']);
    expect(session('pending_google2fa_secret'))->not->toBeNull();
    expect($user->fresh()->google2fa_secret)->toBeNull();
});

test('user can enable mfa using email code', function () {
    $user = User::factory()->create(['google2fa_enabled' => false]);

    // Request code
    $this->actingAs($user)->postJson(route('mfa.send-email-code'));
    $code = Cache::get('mfa_email_code_'.$user->id);

    // Submit to enable route
    $response = $this->actingAs($user)->post(route('mfa.enable'), [
        'one_time_password' => $code,
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('status', 'mfa-enabled');
    expect($user->fresh()->google2fa_enabled)->toBeTrue();
});

test('user can get setup data via ajax', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('mfa.setup-data'));

    $response->assertSuccessful();
    $response->assertJsonStructure(['secret', 'qrCodeInline']);
});

test('user can enable totp via ajax', function () {
    $user = User::factory()->create();

    $user->google2fa_secret = 'GJ2TQMRWG44DQOBX';
    $user->save();

    Google2FA::shouldReceive('verifyKey')
        ->once()
        ->with('GJ2TQMRWG44DQOBX', '123456')
        ->andReturn(true);

    $response = $this->actingAs($user)->postJson(route('mfa.enable-totp-ajax'), [
        'one_time_password' => '123456',
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);
    expect($user->fresh()->google2fa_enabled)->toBeTrue();
});

test('user can enable totp via ajax using pending session secret', function () {
    $user = User::factory()->create();

    // Set pending secret in session
    session(['pending_google2fa_secret' => 'GJ2TQMRWG44DQOBX']);

    Google2FA::shouldReceive('verifyKey')
        ->once()
        ->with('GJ2TQMRWG44DQOBX', '123456')
        ->andReturn(true);

    $response = $this->actingAs($user)->postJson(route('mfa.enable-totp-ajax'), [
        'one_time_password' => '123456',
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);
    expect($user->fresh()->google2fa_enabled)->toBeTrue();
    expect($user->fresh()->google2fa_secret)->toBe('GJ2TQMRWG44DQOBX');
    expect(session('pending_google2fa_secret'))->toBeNull();
});

test('user can disable totp via ajax', function () {
    $user = User::factory()->create(['google2fa_secret' => 'GJ2TQMRWG44DQOBX', 'google2fa_enabled' => true]);

    $response = $this->actingAs($user)->postJson(route('mfa.disable-totp-ajax'));

    $response->assertSuccessful();
    expect($user->fresh()->google2fa_secret)->toBeNull();
});

test('user can enable email mfa via ajax', function () {
    $user = User::factory()->create(['google2fa_enabled' => false]);

    // Request code
    $this->actingAs($user)->postJson(route('mfa.send-email-code'));
    $code = Cache::get('mfa_email_code_'.$user->id);

    $response = $this->actingAs($user)->postJson(route('mfa.enable-email-ajax'), [
        'one_time_password' => $code,
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);
    expect($user->fresh()->google2fa_enabled)->toBeTrue();
});

test('user can disable mfa completely via ajax', function () {
    $user = User::factory()->create(['google2fa_enabled' => true, 'google2fa_secret' => 'GJ2TQMRWG44DQOBX']);

    $response = $this->actingAs($user)->postJson(route('mfa.disable-mfa-ajax'));

    $response->assertSuccessful();
    expect($user->fresh()->google2fa_enabled)->toBeFalse();
    expect($user->fresh()->google2fa_secret)->toBeNull();
});
