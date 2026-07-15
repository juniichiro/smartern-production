<?php

use App\Enums\UserRole;
use App\Models\InternDetail;
use App\Models\Journal;
use App\Models\Office;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::insert([
        ['id' => 1, 'name' => 'Admin'],
        ['id' => 2, 'name' => 'Head Supervisor'],
        ['id' => 3, 'name' => 'Supervisor'],
        ['id' => 4, 'name' => 'School Coordinator'],
        ['id' => 5, 'name' => 'Intern'],
    ]);

    $this->office = Office::create([
        'name' => 'IT Department',
        'latitude' => 14.6507,
        'longitude' => 121.0483,
        'radius_meters' => 100,
    ]);

    $this->supervisor = User::create([
        'role_id' => UserRole::SUPERVISOR,
        'display_id' => '202630001',
        'public_id' => 'test-supervisor-public-id',
        'email' => 'supervisor@example.com',
        'password' => bcrypt('password'),
    ]);

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

it('prefills tasks with pending and revision status regardless of date', function () {
    $date = Carbon::parse('2026-06-17');

    // Create a pending task
    $task1 = Task::create([
        'supervisor_id' => $this->supervisor->id,
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'title' => 'Pending Task',
        'description' => 'Working on it',
        'due_date' => $date->copy()->addDays(10), // Far in the future
        'status' => 'pending',
    ]);

    // Create a revision task
    $task2 = Task::create([
        'supervisor_id' => $this->supervisor->id,
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'title' => 'Revision Task',
        'description' => 'Fix bugs',
        'due_date' => $date->copy()->subDays(10), // Far in the past
        'status' => 'revision',
    ]);

    $response = $this->actingAs($this->user)
        ->withSession(['security_analysis_run' => true, 'mfa_verified' => true])
        ->getJson(route('intern.journal.preview', [
            'type' => 'daily',
            'date' => '2026-06-17',
        ]));

    $response->assertStatus(200)
        ->assertJsonFragment(['title' => 'Pending Task'])
        ->assertJsonFragment(['title' => 'Revision Task']);
});

it('allows removing tasks via included_tasks parameter', function () {
    $date = Carbon::parse('2026-06-17');

    $task1 = Task::create([
        'supervisor_id' => $this->supervisor->id,
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'title' => 'Task 1',
        'description' => 'Desc 1',
        'due_date' => $date,
        'status' => 'pending',
    ]);

    $task2 = Task::create([
        'supervisor_id' => $this->supervisor->id,
        'intern_id' => $this->user->id,
        'office_id' => $this->office->id,
        'title' => 'Task 2',
        'description' => 'Desc 2',
        'due_date' => $date,
        'status' => 'pending',
    ]);

    // Store journal including ONLY Task 1
    $response = $this->actingAs($this->user)
        ->withoutMiddleware()
        ->withSession(['security_analysis_run' => true])
        ->post(route('intern.journal.store'), [
            'journal_type' => 'daily',
            'entry_date' => '2026-06-17',
            'title' => 'Filtered Journal',
            'notes' => 'Some notes',
            'included_tasks' => json_encode([$task1->id]),
        ]);

    $response->assertRedirect();

    $journal = Journal::where('title', 'Filtered Journal')->first();
    expect($journal->content)->toContain('Task 1');
    expect($journal->content)->not->toContain('Task 2');
});
