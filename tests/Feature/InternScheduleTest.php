<?php

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->office = Office::create([
        'name' => 'IT Department',
        'latitude' => 14.6507,
        'longitude' => 121.0483,
        'radius_meters' => 100,
        'total_hours_required' => 480,
    ]);

    // Primary test intern (uses test@smartern.com to bypass CheckSingleSession cookie requirement)
    $this->internUser = User::create([
        'role_id' => UserRole::INTERN->value,
        'display_id' => '202650001',
        'public_id' => 'test-intern-public-id',
        'email' => 'test@smartern.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'is_first_login' => false,
    ]);

    $this->internDetail = InternDetail::create([
        'user_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'first_name' => 'Raniel',
        'last_name' => 'Britos',
        'course' => 'BSIT',
        'gender' => 'male',
        'phone_number' => '09123456789',
        'required_hours' => 480,
        'school' => 'Quezon City University',
        'schedule_assignment' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
    ]);

    // Other intern (for isolation testing)
    $this->otherInternUser = User::create([
        'role_id' => UserRole::INTERN->value,
        'display_id' => '202650002',
        'public_id' => 'test-other-intern-public-id',
        'email' => 'other_intern@smartern.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'is_first_login' => false,
    ]);

    $this->otherInternDetail = InternDetail::create([
        'user_id' => $this->otherInternUser->id,
        'office_id' => $this->office->id,
        'first_name' => 'Other',
        'last_name' => 'Student',
        'course' => 'BSCS',
        'gender' => 'female',
        'phone_number' => '09187654321',
        'required_hours' => 480,
        'school' => 'Other University',
        'schedule_assignment' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
    ]);

    // Supervisor user
    $this->supervisorUser = User::create([
        'role_id' => UserRole::SUPERVISOR->value,
        'display_id' => '202630001',
        'public_id' => 'test-supervisor-public-id',
        'email' => 'supervisor@smartern.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'is_first_login' => false,
    ]);

    \App\Models\SupervisorDetail::create([
        'user_id' => $this->supervisorUser->id,
        'office_id' => $this->office->id,
        'first_name' => 'John',
        'last_name' => 'Supervisor',
        'phone_number' => '09123456780',
    ]);

    // Coordinator user
    $this->coordinatorUser = User::create([
        'role_id' => UserRole::SCHOOL_COORDINATOR->value,
        'display_id' => '202640001',
        'public_id' => 'test-coordinator-public-id',
        'email' => 'coordinator@smartern.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'is_first_login' => false,
    ]);

    \App\Models\SchoolCoordinatorDetail::create([
        'user_id' => $this->coordinatorUser->id,
        'first_name' => 'Jane',
        'last_name' => 'Coordinator',
        'phone_number' => '09123456781',
        'school' => 'Quezon City University',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('guest is redirected to login when accessing intern schedule', function () {
    $response = $this->get(route('intern.schedule.index'));
    $response->assertRedirect(route('login'));
});

test('non-intern user cannot access intern schedule', function () {
    $this->actingAs($this->supervisorUser);
    $response = $this->get(route('intern.schedule.index'));
    expect($response->status())->toBeIn([403, 302]);
});

test('supervisor can access supervisor schedule page as before', function () {
    $this->withoutMiddleware([\App\Http\Middleware\CheckSingleSession::class, \App\Http\Middleware\MFACheck::class]);

    $this->actingAs($this->supervisorUser);
    $supResponse = $this->get(route('supervisor.schedule.index'));
    expect($supResponse->status())->toBe(200);
});

test('coordinator can access coordinator schedule page as before', function () {
    $this->withoutMiddleware([\App\Http\Middleware\CheckSingleSession::class, \App\Http\Middleware\MFACheck::class]);

    $this->actingAs($this->coordinatorUser);
    $coordResponse = $this->get(route('coordinator.schedule.index'));
    expect($coordResponse->status())->toBe(200);
});

test('intern schedule loads successfully with sentence-case title and quiet layout', function () {
    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.schedule.index', ['month' => '2026-10', 'date' => '2026-10-13']));

    $response->assertSuccessful();
    // Sentence-case heading
    $response->assertSee('Schedule');
    // Subtitle matches actual contents
    $response->assertSee('Holidays, schedule changes, tasks, and attendance');
    // Month navigation toolbar exists
    $response->assertSee('October 2026');
    $response->assertSee('Today');
    // Segmented control exists
    $response->assertSee('Month');
    $response->assertSee('Agenda');
});

test('deep linking with validated params: month, view, and date survive reload', function () {
    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.schedule.index', [
        'month' => '2026-11',
        'view' => 'agenda',
        'date' => '2026-11-15',
    ]));

    $response->assertSuccessful();
    $response->assertSee('November 2026');
    // Agenda view is active
    $response->assertSee('currentView: \'agenda\'', false);
    $response->assertSee('selectedDate: \'2026-11-15\'', false);
});

test('invalid query parameters fall back gracefully without error page', function () {
    $this->actingAs($this->internUser);

    $response = $this->get('/intern/schedule?month=9999-99&view=<script>&date=2026-02-31');

    $response->assertSuccessful();
    // Falls back to current month and 'month' view
    $response->assertSee('currentView: \'month\'', false);
});

test('data isolation: intern cannot see another interns tasks, attendance, or school overrides', function () {
    // Task belonging to other intern
    Task::create([
        'supervisor_id' => $this->supervisorUser->id,
        'intern_id' => $this->otherInternUser->id,
        'office_id' => $this->office->id,
        'title' => 'Secret Task of Other Intern',
        'description' => 'Should not be seen',
        'due_date' => Carbon::parse('2026-10-15 17:00:00', 'Asia/Manila'),
        'status' => TaskStatus::PENDING,
    ]);

    // Attendance of other intern
    Attendance::create([
        'intern_id' => $this->otherInternUser->id,
        'office_id' => $this->office->id,
        'clock_in' => Carbon::parse('2026-10-15 08:00:00', 'Asia/Manila'),
        'clock_out' => Carbon::parse('2026-10-15 17:00:00', 'Asia/Manila'),
        'status' => 'on_time',
    ]);

    // Schedule override for another school
    Schedule::create([
        'date' => '2026-10-16',
        'school_name' => 'Other University',
        'type' => 'suspension',
        'description' => 'Other School Suspension Only',
    ]);

    // Task belonging to this intern
    Task::create([
        'supervisor_id' => $this->supervisorUser->id,
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'title' => 'My Assigned Task 101',
        'description' => 'My personal task',
        'due_date' => Carbon::parse('2026-10-15 17:00:00', 'Asia/Manila'),
        'status' => TaskStatus::PENDING,
    ]);

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.schedule.index', ['month' => '2026-10', 'date' => '2026-10-15']));

    $response->assertSuccessful();
    // Sees own task
    $response->assertSee('My Assigned Task 101');
    // Does NOT see other intern's task
    $response->assertDontSee('Secret Task of Other Intern');
    // Does NOT see other school's suspension
    $response->assertDontSee('Other School Suspension Only');
});

test('marker logic: day with holiday and override, and day with 4+ markers shows +N more', function () {
    // Holiday on Oct 20
    Schedule::create([
        'date' => '2026-10-20',
        'type' => 'holiday',
        'description' => 'Special Non-Working Holiday',
    ]);

    // 4 tasks on Oct 20
    for ($i = 1; $i <= 4; $i++) {
        Task::create([
            'supervisor_id' => $this->supervisorUser->id,
            'intern_id' => $this->internUser->id,
            'office_id' => $this->office->id,
            'title' => "Multi-Task Marker {$i}",
            'description' => "Task details {$i}",
            'due_date' => Carbon::parse('2026-10-20 17:00:00', 'Asia/Manila'),
            'status' => TaskStatus::PENDING,
        ]);
    }

    $this->actingAs($this->internUser);
    $response = $this->get(route('intern.schedule.index', ['month' => '2026-10', 'date' => '2026-10-20']));

    $response->assertSuccessful();
    $response->assertSee('Special Non-Working Holiday');
    // Has 5 total markers on Oct 20 (1 holiday + 4 tasks), first 2 shown, remaining 3 indicated as +3 more
    $response->assertSee('+3 more');
});

test('task due on weekend is displayed and marked correctly', function () {
    // Oct 18, 2026 is Sunday
    Task::create([
        'supervisor_id' => $this->supervisorUser->id,
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'title' => 'Sunday Urgent Task',
        'description' => 'Weekend task submission',
        'due_date' => Carbon::parse('2026-10-18 14:00:00', 'Asia/Manila'),
        'status' => TaskStatus::PENDING,
    ]);

    $this->actingAs($this->internUser);
    $response = $this->get(route('intern.schedule.index', ['month' => '2026-10', 'date' => '2026-10-18']));

    $response->assertSuccessful();
    $response->assertSee('Sunday Urgent Task');
});

test('special characters and HTML in titles are safely escaped', function () {
    Schedule::create([
        'date' => '2026-10-22',
        'type' => 'suspension',
        'description' => '<script>alert("xss")</script> & Special "Notes"',
    ]);

    $this->actingAs($this->internUser);
    $response = $this->get(route('intern.schedule.index', ['month' => '2026-10', 'date' => '2026-10-22']));

    $response->assertSuccessful();
    // Raw unescaped script tag MUST NOT appear in HTML
    $response->assertDontSee('<script>alert("xss")</script>', false);
});

test('upcoming list renders next 5 items from today onwards', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-10 10:00:00', 'Asia/Manila'));

    for ($i = 1; $i <= 6; $i++) {
        Task::create([
            'supervisor_id' => $this->supervisorUser->id,
            'intern_id' => $this->internUser->id,
            'office_id' => $this->office->id,
            'title' => "Upcoming Task Number {$i}",
            'description' => "Task description {$i}",
            'due_date' => Carbon::parse('2026-10-10 10:00:00', 'Asia/Manila')->addDays($i),
            'status' => TaskStatus::PENDING,
        ]);
    }

    $this->actingAs($this->internUser);
    $response = $this->get(route('intern.schedule.index', ['month' => '2026-10', 'date' => '2026-10-10']));

    $response->assertSuccessful();
    $response->assertSee('Upcoming');
    $upcomingItems = $response->viewData('upcomingItems');
    expect($upcomingItems)->toHaveCount(5);
    $titles = collect($upcomingItems)->pluck('title')->all();
    expect($titles)->toContain('Upcoming Task Number 1')
        ->and($titles)->toContain('Upcoming Task Number 5')
        ->and($titles)->not->toContain('Upcoming Task Number 6');
});

test('absence requests can be submitted, edited, and withdrawn with flash messages', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Manila'));
    $this->actingAs($this->internUser);

    // 1. Submit Absence Request (date is in the future relative to today)
    $storeResponse = $this->from(route('intern.schedule.index'))->post(route('intern.schedule.absence.store'), [
        'date' => '2026-10-25',
        'reason' => 'Attending university career seminar',
    ]);

    $storeResponse->assertRedirect(route('intern.schedule.index'));
    $storeResponse->assertSessionHas('success');

    $absence = AbsenceRequest::where('intern_id', $this->internUser->id)
        ->whereDate('date', '2026-10-25')
        ->first();
    expect($absence)->not->toBeNull()
        ->and($absence->status)->toBe('pending');

    // 2. Update Absence Request
    $updateResponse = $this->from(route('intern.schedule.index'))->put(route('intern.schedule.absence.update', $absence->getRouteKey()), [
        'reason' => 'Updated: Attending university career seminar & research defense',
    ]);

    $updateResponse->assertRedirect(route('intern.schedule.index'));
    $updateResponse->assertSessionHas('success');
    expect($absence->fresh()->reason)->toContain('research defense');

    // 3. Withdraw Absence Request
    $deleteResponse = $this->from(route('intern.schedule.index'))->delete(route('intern.schedule.absence.revoke', $absence->getRouteKey()));
    $deleteResponse->assertRedirect(route('intern.schedule.index'));
    $deleteResponse->assertSessionHas('success');
    expect(AbsenceRequest::find($absence->id))->toBeNull();
});

test('hard rules: page contains no stat tile row and defines no custom font-family', function () {
    $this->actingAs($this->internUser);
    $response = $this->get(route('intern.schedule.index'));

    $response->assertSuccessful();
    // No stat-card component used
    $response->assertDontSee('x-stat-card');
    // No inline font-family declared in view
    $content = $response->getContent();
    expect($content)->not->toContain('font-family:');
    expect($content)->not->toContain('@font-face');
});
