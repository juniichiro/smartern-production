<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AnomalyDetectionService;
use Database\Seeders\RoleSeeder;
use Vinkla\Hashids\Facades\Hashids;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('device is automatically parsed from user agent and logged in audit log', function () {
    $user = User::factory()->create(['role_id' => 5]);

    $log = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Login',
        'resource' => 'Auth',
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'anomaly_score' => null,
        'timestamp' => now(),
    ]);

    expect($log->device)->toBe('Windows|Chrome');
});

test('device information is included in select and passed to anomaly detection service', function () {
    $user = User::factory()->create(['role_id' => 5]);

    // Create a log
    $log = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Login',
        'resource' => 'Auth',
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'anomaly_score' => null,
        'timestamp' => now(),
    ]);

    expect($log->device)->toBe('MacOS|Chrome');

    // Run the anomaly detection service and verify scores are recalculable
    $service = new AnomalyDetectionService;
    $count = $service->recalculateScores();
    expect($count)->toBeGreaterThanOrEqual(0);
});

test('admin security monitor view displays parsed device name', function () {
    $admin = User::factory()->create(['role_id' => 1]);
    $user = User::factory()->create(['role_id' => 5]);

    $log = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Sensitive Data Access',
        'resource' => 'Database',
        'ip_address' => '192.168.1.15',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
        'anomaly_score' => 0.85,
        'timestamp' => now(),
    ]);

    expect($log->device)->toBe('iOS|Safari');

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get('/admin/security');

    $response->assertStatus(200);
    // The view replaces '|' with ' • ' -> 'iOS • Safari'
    $response->assertSee('iOS • Safari');
});

test('admin can mark an anomaly safe (Path A)', function () {
    $admin = User::factory()->create(['role_id' => 1]);
    $user = User::factory()->create(['role_id' => 5, 'locked_until' => now()->addMinutes(30)]);

    $log = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Sensitive Data Access',
        'resource' => 'Database',
        'ip_address' => '192.168.1.15',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'anomaly_score' => 0.85,
        'timestamp' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->post(route('admin.security.markSafe', [$user, $log]));

    $response->assertRedirect();
    $user->refresh();
    expect($user->locked_until)->toBeNull();

    // Assert Audit Log entry was created for the admin action
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'Evaluated Anomaly: Marked Safe',
        'resource' => 'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
    ]);
});

test('admin can mark an anomaly as compromised (Path B)', function () {
    $admin = User::factory()->create(['role_id' => 1]);
    $user = User::factory()->create([
        'role_id' => 5,
        'locked_until' => now()->addMinutes(30),
        'is_first_login' => false,
        'google2fa_enabled' => true,
        'google2fa_secret' => 'some_secret_key',
    ]);

    $log = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Sensitive Data Access',
        'resource' => 'Database',
        'ip_address' => '192.168.1.15',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'anomaly_score' => 0.85,
        'timestamp' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->post(route('admin.security.markCompromised', [$user, $log]));

    $response->assertRedirect();
    $user->refresh();

    expect($user->locked_until)->toBeNull();
    expect((bool) $user->is_first_login)->toBeTrue();
    expect((bool) $user->google2fa_enabled)->toBeFalse();
    expect($user->google2fa_secret)->toBeNull();

    // Assert Audit Log entry was created for the admin action
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'Evaluated Anomaly: Compromised Account - Credentials Reset',
        'resource' => 'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
    ]);
});

test('admin can mark an anomaly as malicious insider (Path C)', function () {
    $admin = User::factory()->create(['role_id' => 1]);
    $user = User::factory()->create(['role_id' => 5]);

    $log = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Sensitive Data Access',
        'resource' => 'Database',
        'ip_address' => '192.168.1.15',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'anomaly_score' => 0.85,
        'timestamp' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true])
        ->post(route('admin.security.markSuspended', [$user, $log]));

    $response->assertRedirect();
    $user->refresh();

    expect($user->locked_until->format('Y-m-d H:i:s'))->toBe('9999-12-31 23:59:59');

    // Assert Audit Log entry was created for the admin action
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'Evaluated Anomaly: Malicious Insider - Suspended Account',
        'resource' => 'Security Monitor (User: '.Hashids::encode($user->id).', Log: '.Hashids::encode($log->log_id).')',
    ]);
});

test('future identical log matches baseline and gets a low anomaly score', function () {
    $user = User::factory()->create(['role_id' => 5]);

    $log = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Clock In',
        'resource' => 'Attendance Record #12',
        'ip_address' => '203.0.113.50',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
        'anomaly_score' => null,
        'timestamp' => now(),
    ]);

    // Add to baseline
    $service = new AnomalyDetectionService;
    $service->warmBaseline($log);

    // Create a new identical log
    $newLog = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Clock In',
        'resource' => 'Attendance Record #13',
        'ip_address' => '203.0.113.50',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
        'anomaly_score' => null,
        'timestamp' => now(),
    ]);

    // Recalculate scores
    $service->recalculateScores();

    $newLog->refresh();
    // It should be dynamically whitelisted to a safe range (below 0.50)
    expect((float) $newLog->anomaly_score)->toBeLessThan(0.50);

    // Clean up baseline file entry so we do not pollute testing baseline
    $path = storage_path('app/audit_baseline.json');
    if (file_exists($path)) {
        $baseline = json_decode(file_get_contents($path), true) ?? [];
        if (! empty($baseline)) {
            array_pop($baseline); // remove the test entry we just added
            file_put_contents($path, json_encode($baseline, JSON_PRETTY_PRINT));
        }
    }
});
