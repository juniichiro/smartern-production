<?php

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    // Ensure the archive directory is clean or empty before the test
    $archiveDirectory = storage_path('app/audit_archives');
    if (File::isDirectory($archiveDirectory)) {
        File::cleanDirectory($archiveDirectory);
    }
});

afterEach(function () {
    // Cleanup generated files
    $archiveDirectory = storage_path('app/audit_archives');
    if (File::isDirectory($archiveDirectory)) {
        File::cleanDirectory($archiveDirectory);
    }
});

test('it successfully archives and prunes old logs while retaining hot logs', function () {
    $knownDate = now();
    $this->travelTo($knownDate);

    $user = User::factory()->create(['role_id' => 5]);

    // Create 1: An old log (100 days old) -> should be archived & pruned
    $oldLog = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Login',
        'resource' => 'Auth',
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'anomaly_score' => null,
        'timestamp' => now()->subDays(100),
    ]);

    // Create 2: A hot log (10 days old) -> should remain in DB
    $hotLog = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Clock-In',
        'resource' => 'Attendance',
        'ip_address' => '192.168.1.2',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'anomaly_score' => null,
        'timestamp' => now()->subDays(10),
    ]);

    // Verify initial database state
    $this->assertDatabaseHas('audit_logs', ['log_id' => $oldLog->log_id]);
    $this->assertDatabaseHas('audit_logs', ['log_id' => $hotLog->log_id]);

    // Run the command
    $this->artisan('app:archive-audit-logs --days=90')
        ->expectsOutput('Archiving and pruning audit logs older than 90 days (cutoff: '.$knownDate->copy()->subDays(90)->toDateTimeString().')...')
        ->expectsOutput('Compressing 1 logs into Gzip archive...')
        ->expectsOutput('Pruning archived logs from the database...')
        ->assertExitCode(0);

    // Verify database state after pruning
    $this->assertDatabaseMissing('audit_logs', ['log_id' => $oldLog->log_id]);
    $this->assertDatabaseHas('audit_logs', ['log_id' => $hotLog->log_id]);

    // Verify archive file was created
    $archiveDirectory = storage_path('app/audit_archives');
    $files = File::files($archiveDirectory);
    expect($files)->toHaveCount(1);

    $archiveFile = $files[0];
    expect($archiveFile->getExtension())->toBe('gz');

    // Read and decompress Gzip file content
    $gzContent = gzdecode(file_get_contents($archiveFile->getRealPath()));
    expect($gzContent)->not->toBeEmpty();

    // Verify Gzip contains the old log but not the hot log
    $archivedLogs = json_decode($gzContent, true);
    expect($archivedLogs)->toHaveCount(1);
    expect($archivedLogs[0]['log_id'])->toBe($oldLog->log_id);
    expect($archivedLogs[0]['action'])->toBe('Login');

    $this->travelBack();
});

test('it outputs a message and does not create an archive when no logs are older than threshold', function () {
    $knownDate = now();
    $this->travelTo($knownDate);

    $user = User::factory()->create(['role_id' => 5]);

    // Create only a hot log (10 days old)
    $hotLog = AuditLog::create([
        'user_id' => $user->id,
        'action' => 'Clock-In',
        'resource' => 'Attendance',
        'ip_address' => '192.168.1.2',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'anomaly_score' => null,
        'timestamp' => now()->subDays(10),
    ]);

    // Run command
    $this->artisan('app:archive-audit-logs --days=90')
        ->expectsOutput('No audit logs found older than the specified retention threshold.')
        ->assertExitCode(0);

    // DB remains unchanged
    $this->assertDatabaseHas('audit_logs', ['log_id' => $hotLog->log_id]);

    // No files in archives folder
    $archiveDirectory = storage_path('app/audit_archives');
    if (File::isDirectory($archiveDirectory)) {
        $files = File::files($archiveDirectory);
        expect($files)->toBeEmpty();
    }

    $this->travelBack();
});
