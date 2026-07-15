<?php

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Journal;
use App\Models\Office;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // 0. Setup Roles
    Role::insert(['id' => 1, 'name' => 'Admin']);
    Role::insert(['id' => 2, 'name' => 'Head Supervisor']);
    Role::insert(['id' => 3, 'name' => 'Supervisor']);
    Role::insert(['id' => 4, 'name' => 'School Coordinator']);
    Role::insert(['id' => 5, 'name' => 'Intern']);

    // 1. Setup Office
    $this->office = Office::create([
        'name' => 'IT Department',
        'latitude' => 14.6507,
        'longitude' => 121.0483,
        'radius_meters' => 100,
    ]);

    // 2. Setup Supervisor
    $this->supervisor = User::create([
        'role_id' => UserRole::SUPERVISOR,
        'display_id' => '202630001',
        'public_id' => 'test-supervisor-public-id',
        'email' => 'supervisor@example.com',
        'password' => bcrypt('password'),
    ]);

    // 3. Setup User (Intern)
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

test('intern can preview daily journal data with tasks and attendance', function () {
    $date = Carbon::parse('2026-06-17');

    // Create a task due on that date
    Task::create([
        'supervisor_id' => $this->supervisor->id,
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'title' => 'Build Login Form',
        'description' => 'Create a secure login page',
        'due_date' => $date,
        'status' => 'pending',
    ]);

    // Create attendance on that date
    Attendance::create([
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'clock_in' => $date->copy()->hour(8)->minute(0),
        'clock_out' => $date->copy()->hour(17)->minute(0),
        'status' => 'on_time',
        'remarks' => 'Completed building UI elements.',
    ]);

    $response = $this->actingAs($this->user)
        ->withSession(['security_analysis_run' => true, 'mfa_verified' => true])
        ->getJson(route('intern.journal.preview', [
            'type' => 'daily',
            'date' => '2026-06-17',
        ]));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'suggested_title' => 'Daily Journal - June 17, 2026',
        ])
        ->assertJsonFragment([
            'title' => 'Build Login Form',
            'status' => 'Pending',
        ])
        ->assertJsonFragment([
            'status' => 'On time',
            'remarks' => 'Completed building UI elements.',
        ]);
});

test('intern can store daily journal which compiles activities', function () {
    $date = Carbon::parse('2026-06-17');

    // Create a task due on that date
    Task::create([
        'supervisor_id' => $this->supervisor->id,
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'title' => 'Build Login Form',
        'description' => 'Create a secure login page',
        'due_date' => $date,
        'status' => 'pending',
    ]);

    // Create attendance on that date
    Attendance::create([
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'clock_in' => $date->copy()->hour(8)->minute(0),
        'clock_out' => $date->copy()->hour(17)->minute(0),
        'status' => 'on_time',
        'remarks' => 'Completed building UI elements.',
    ]);

    $response = $this->actingAs($this->user)
        ->withSession(['mfa_verified' => true])
        ->post(route('intern.journal.store'), [
            'journal_type' => 'daily',
            'entry_date' => '2026-06-17',
            'title' => 'My first daily log',
            'notes' => 'Today was a productive day. I solved several bugs.',
        ]);

    $response->assertRedirect(route('intern.progress').'#journals');

    // Assert that the journal is stored in the database
    $journal = Journal::where('intern_id', $this->user->id)->first();
    expect($journal)->not->toBeNull();
    expect($journal->journal_type)->toBe('daily');
    expect($journal->title)->toBe('My first daily log');
    expect($journal->notes)->toBe('Today was a productive day. I solved several bugs.');
    expect($journal->content)->toContain('DAILY LOG SUMMARY (June 17, 2026)');
    expect($journal->content)->toContain('Build Login Form [Status: Pending]');
    expect($journal->content)->toContain('Completed building UI elements.');
    expect($journal->content)->toContain('Today was a productive day. I solved several bugs.');
});

test('intern can preview and store weekly journal data', function () {
    $date = Carbon::parse('2026-06-17');

    Task::create([
        'supervisor_id' => $this->supervisor->id,
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'title' => 'Weekly Task',
        'description' => 'Weekly task description',
        'due_date' => $date,
        'status' => 'completed',
    ]);

    Attendance::create([
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'clock_in' => $date->copy()->hour(8)->minute(0),
        'clock_out' => $date->copy()->hour(17)->minute(0),
        'status' => 'on_time',
        'remarks' => 'Daily attendance remark.',
    ]);

    $responsePreview = $this->actingAs($this->user)
        ->withSession(['security_analysis_run' => true, 'mfa_verified' => true])
        ->getJson(route('intern.journal.preview', [
            'type' => 'weekly',
            'date' => '2026-06-17',
        ]));

    $responsePreview->assertStatus(200)
        ->assertJsonFragment([
            'suggested_title' => 'Weekly Journal - Week of June 15, 2026',
        ])
        ->assertJsonFragment([
            'title' => 'Weekly Task',
            'status' => 'Verified',
        ])
        ->assertJsonFragment([
            'status' => 'On time',
            'remarks' => 'Daily attendance remark.',
        ]);

    $responseStore = $this->actingAs($this->user)
        ->withSession(['mfa_verified' => true])
        ->post(route('intern.journal.store'), [
            'journal_type' => 'weekly',
            'entry_date' => '2026-06-17',
            'title' => 'My first weekly log',
            'notes' => 'Weekly log notes here.',
        ]);

    $responseStore->assertRedirect(route('intern.progress').'#journals');

    $journal = Journal::where('intern_id', $this->user->id)
        ->where('journal_type', 'weekly')
        ->first();

    expect($journal)->not->toBeNull();
    expect($journal->title)->toBe('My first weekly log');
    expect($journal->notes)->toBe('Weekly log notes here.');
    expect($journal->content)->toContain('WEEKLY LOG SUMMARY (Week of June 15, 2026 to June 21, 2026)');
    expect($journal->content)->toContain('Weekly Task [Status: Verified]');
    expect($journal->content)->toContain('Daily attendance remark.');
    expect($journal->content)->toContain('Weekly log notes here.');
});

test('intern can update their journal entry title and notes', function () {
    $journal = new Journal;
    $journal->intern_id = $this->user->id;
    $journal->journal_type = 'daily';
    $journal->entry_date = '2026-06-17';
    $journal->title = 'Initial Title';
    $journal->notes = 'Initial notes';
    $journal->content = 'Initial compiled content';
    $journal->save();

    $response = $this->actingAs($this->user)
        ->withSession(['mfa_verified' => true])
        ->put(route('intern.journal.update', $journal), [
            'title' => 'Updated Title',
            'notes' => 'Updated personal notes',
        ]);

    $response->assertRedirect(route('intern.progress').'#journals');

    $journal->refresh();
    expect($journal->title)->toBe('Updated Title');
    expect($journal->notes)->toBe('Updated personal notes');
    expect($journal->content)->toContain('DAILY LOG SUMMARY (June 17, 2026)');
    expect($journal->content)->toContain('Updated personal notes');
});

test('daily journal is automatically created on clock out', function () {
    $date = Carbon::parse('2026-06-17'); // Wednesday
    Carbon::setTestNow($date->copy()->hour(17)->minute(0));

    Attendance::create([
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'clock_in' => $date->copy()->hour(8)->minute(0),
        'status' => 'on_time',
    ]);

    $service = app(AttendanceService::class);
    $result = $service->recordAttendance($this->user->id);

    expect($result['success'])->toBeTrue();
    expect($result['type'])->toBe('out');

    $journal = Journal::where('intern_id', $this->user->id)
        ->where('journal_type', 'daily')
        ->first();

    expect($journal)->not->toBeNull();
    expect($journal->entry_date->toDateString())->toBe($date->toDateString());
    expect($journal->title)->toBe('Daily Journal - June 17, 2026');
    expect($journal->content)->toContain('DAILY LOG SUMMARY (June 17, 2026)');

    Carbon::setTestNow();
});

test('weekly journal is automatically created on Friday clock out', function () {
    $date = Carbon::parse('2026-06-19'); // Friday
    Carbon::setTestNow($date->copy()->hour(17)->minute(0));

    Attendance::create([
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'clock_in' => $date->copy()->hour(8)->minute(0),
        'status' => 'on_time',
    ]);

    $service = app(AttendanceService::class);
    $result = $service->recordAttendance($this->user->id);

    expect($result['success'])->toBeTrue();
    expect($result['type'])->toBe('out');

    $startOfWeek = $date->copy()->startOfWeek()->format('Y-m-d');
    $journal = Journal::where('intern_id', $this->user->id)
        ->where('journal_type', 'weekly')
        ->first();

    expect($journal)->not->toBeNull();
    expect($journal->entry_date->toDateString())->toBe($startOfWeek);
    expect($journal->title)->toBe('Weekly Journal - Week of June 15, 2026');
    expect($journal->content)->toContain('WEEKLY LOG SUMMARY (Week of June 15, 2026 to June 21, 2026)');

    Carbon::setTestNow();
});
