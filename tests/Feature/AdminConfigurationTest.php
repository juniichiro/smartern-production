<?php

use App\Enums\UserRole;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\BackupHealthService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('local');
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-10-03 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow(null);
});

test('admin can access the system configuration page', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/test_backup.zip',
        'file_size' => '25.00 MB',
        'status' => 'success',
        'sha256_hash' => 'abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get(route('admin.configuration'));

    $response->assertOk();
    $response->assertViewIs('admin.configuration');
    $response->assertSee('System configuration');
    $response->assertSee('Backups and data protection');
    $response->assertSee('Backups');
    $response->assertSee('Upload snapshot');
    $response->assertSee('Back up now');
});

test('non-admin is forbidden from accessing system configuration', function () {
    $intern = User::factory()->create([
        'role_id' => UserRole::INTERN,
    ]);

    $response = $this->actingAs($intern)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get(route('admin.configuration'));

    $response->assertForbidden();
});

test('guest is redirected to login from system configuration', function () {
    $response = $this->get(route('admin.configuration'));

    $response->assertRedirect(route('login'));
});

test('backup health service detects healthy state when recent backup exists', function () {
    Config::set('backup.health.warn_after_hours', 36);
    Config::set('backup.health.critical_after_hours', 72);

    $backup = SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/backup_1.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'hash1',
    ]);
    $backup->created_at = Carbon::now()->subHours(5);
    $backup->save();

    $service = app(BackupHealthService::class);
    $health = $service->getHealthState();

    expect($health['status'])->toBe('healthy');
    expect($health['level'])->toBe('success');
    expect($health['is_stale'])->toBeFalse();
    expect($health['is_critical'])->toBeFalse();
    expect($health['headline'])->toContain('Backups are healthy');
});

test('backup health service detects stale state when beyond warn threshold', function () {
    Config::set('backup.health.warn_after_hours', 36);
    Config::set('backup.health.critical_after_hours', 72);

    $backup = SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/backup_1.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'hash1',
    ]);
    $backup->created_at = Carbon::now()->subHours(40);
    $backup->save();

    $service = app(BackupHealthService::class);
    $health = $service->getHealthState();

    expect($health['status'])->toBe('stale');
    expect($health['level'])->toBe('warning');
    expect($health['is_stale'])->toBeTrue();
    expect($health['is_critical'])->toBeFalse();
    expect($health['headline'])->toContain('Last successful backup was 40 hours ago');
});

test('backup health service detects critical state when beyond critical threshold', function () {
    Config::set('backup.health.warn_after_hours', 36);
    Config::set('backup.health.critical_after_hours', 72);

    $backup = SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/backup_1.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'hash1',
    ]);
    $backup->created_at = Carbon::now()->subDays(76);
    $backup->save();

    $service = app(BackupHealthService::class);
    $health = $service->getHealthState();

    expect($health['status'])->toBe('critical');
    expect($health['level'])->toBe('critical');
    expect($health['is_stale'])->toBeTrue();
    expect($health['is_critical'])->toBeTrue();
    expect($health['headline'])->toContain('76 days ago');
});

test('backup health service detects failed latest backup', function () {
    $backupSuccess = SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/backup_1.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'hash1',
    ]);
    $backupSuccess->created_at = Carbon::now()->subDays(2);
    $backupSuccess->save();

    $backupFailed = SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/backup_failed.zip',
        'file_size' => null,
        'status' => 'failed',
        'sha256_hash' => null,
    ]);
    $backupFailed->created_at = Carbon::now()->subHours(1);
    $backupFailed->save();

    $service = app(BackupHealthService::class);
    $health = $service->getHealthState();

    expect($health['status'])->toBe('failed');
    expect($health['level'])->toBe('failed');
    expect($health['headline'])->toBe('The last backup failed');
});

test('backup health service detects running backup', function () {
    SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/backup_running.zip',
        'file_size' => null,
        'status' => 'in_progress',
        'sha256_hash' => null,
    ]);

    $service = app(BackupHealthService::class);
    $health = $service->getHealthState();

    expect($health['status'])->toBe('running');
    expect($health['level'])->toBe('info');
    expect($health['headline'])->toBe('Backup in progress');
});

test('backup health service detects critical state when all existing backups have failed', function () {
    SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/failed_1.zip',
        'file_size' => null,
        'status' => 'failed',
        'sha256_hash' => null,
    ]);

    $service = app(BackupHealthService::class);
    $health = $service->getHealthState();

    expect($health['status'])->toBe('failed');
    expect($health['level'])->toBe('failed');
    expect($health['headline'])->toBe('The last backup failed');
});

test('backup health service detects empty state when no backups exist', function () {
    $service = app(BackupHealthService::class);
    $health = $service->getHealthState();

    expect($health['status'])->toBe('empty');
    expect($health['headline'])->toBe('No backups yet');
});

test('backup health service identifies only successful backup', function () {
    $backup1 = SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/backup_1.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'hash1',
    ]);

    $service = app(BackupHealthService::class);
    expect($service->isOnlySuccessfulBackup($backup1))->toBeTrue();

    // Adding second backup
    $backup2 = SystemBackup::create([
        'backup_type' => 'manual',
        'file_path' => 'backups/backup_2.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'hash2',
    ]);

    expect($service->isOnlySuccessfulBackup($backup1))->toBeFalse();
    expect($service->isOnlySuccessfulBackup($backup2))->toBeFalse();
});

test('admin can filter backups by type auto and manual', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    $bAuto = SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/auto_1.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'hash_auto_1234567890abcdef1234567890abcdef',
    ]);
    $bAuto->created_at = Carbon::now()->subDay();
    $bAuto->save();

    $bManual = SystemBackup::create([
        'backup_type' => 'manual',
        'file_path' => 'backups/manual_1.zip',
        'file_size' => '12 MB',
        'status' => 'success',
        'sha256_hash' => 'hash_manual_1234567890abcdef1234567890abcdef',
    ]);
    $bManual->created_at = Carbon::now();
    $bManual->save();

    // Filter auto
    $responseAuto = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get(route('admin.configuration', ['filter' => 'auto']));

    $responseAuto->assertOk();
    $responseAuto->assertSee('hash_a…bcdef');
    $responseAuto->assertDontSee('hash_m…bcdef');

    // Filter manual
    $responseManual = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get(route('admin.configuration', ['filter' => 'manual']));

    $responseManual->assertOk();
    $responseManual->assertSee('hash_m…bcdef');
    $responseManual->assertDontSee('hash_a…bcdef');
});

test('admin can search backups by checksum prefix', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/target.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => 'fd9904406bfa847ce429c4d6a65542a26052ade6acc09f84c71ad87c039ad3e6',
    ]);

    SystemBackup::create([
        'backup_type' => 'auto',
        'file_path' => 'backups/other.zip',
        'file_size' => '10 MB',
        'status' => 'success',
        'sha256_hash' => '112233445566778899aabbccddeeff00112233445566778899aabbccddeeff00',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get(route('admin.configuration', ['search' => 'fd9904']));

    $response->assertOk();
    $response->assertSee('fd9904…ad3e6');
    $response->assertDontSee('112233…eff00');
});

test('invalid filter values are rejected by validation', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get(route('admin.configuration', ['filter' => 'malicious_input']));

    $response->assertSessionHasErrors(['filter']);
});

test('admin can trigger manual backup via trigger endpoint', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    Artisan::shouldReceive('call')
        ->once()
        ->with('backup:run', [
            '--type' => 'manual',
            '--admin_id' => $admin->id,
        ])
        ->andReturn(0);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->post(route('admin.backup.trigger'));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('admin can download backup file', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    Storage::disk('local')->put('backups/test.zip', 'dummy-zip-content');

    $backup = SystemBackup::create([
        'backup_type' => 'manual',
        'file_path' => 'backups/test.zip',
        'file_size' => '15 B',
        'status' => 'success',
        'sha256_hash' => hash('sha256', 'dummy-zip-content'),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->get(route('admin.backup.download', $backup));

    $response->assertOk();
});

test('admin can delete a backup and its storage file is cleaned up', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    Storage::disk('local')->put('backups/to_delete.zip', 'content-to-delete');

    $backup = SystemBackup::create([
        'backup_type' => 'manual',
        'file_path' => 'backups/to_delete.zip',
        'file_size' => '17 B',
        'status' => 'success',
        'sha256_hash' => 'somehash',
    ]);

    expect(Storage::disk('local')->exists('backups/to_delete.zip'))->toBeTrue();

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->delete(route('admin.backup.destroy', $backup));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Backup record withdrawn.');
    expect(Storage::disk('local')->exists('backups/to_delete.zip'))->toBeFalse();
    expect(SystemBackup::find($backup->backup_id))->toBeNull();
});

test('upload snapshot rejects non-zip files', function () {
    $admin = User::factory()->create([
        'email' => 'test@smartern.com',
        'role_id' => UserRole::ADMIN,
    ]);

    $file = UploadedFile::fake()->create('malicious.exe', 1000);

    $response = $this->actingAs($admin)
        ->withSession(['mfa_verified' => true, 'security_analysis_run' => true])
        ->post(route('admin.backup.upload'), [
            'backup_zip' => $file,
        ]);

    $response->assertSessionHasErrors(['backup_zip']);
});
