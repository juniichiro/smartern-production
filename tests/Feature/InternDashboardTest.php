<?php

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Office;
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
        'school' => 'Quezon City University',
        'hours_rendered' => 120.0,
        'required_hours' => 480.0,
        'start_date' => Carbon::now()->subWeeks(4)->toDateString(),
    ]);

    $this->supervisorUser = User::create([
        'role_id' => UserRole::SUPERVISOR->value,
        'display_id' => '202630001',
        'public_id' => 'test-supervisor-public-id',
        'email' => 'supervisor@smartern.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'is_first_login' => false,
    ]);
});

afterEach(function () {
    Carbon::setTestNow(); // Clear frozen test time
});

test('guest is redirected to login when accessing intern dashboard', function () {
    $response = $this->get(route('intern.dashboard'));

    $response->assertRedirect(route('login'));
});

test('non-intern user cannot access intern dashboard', function () {
    $this->actingAs($this->supervisorUser);

    $response = $this->get(route('intern.dashboard'));

    expect($response->status())->toBeIn([403, 302]);
});

test('intern dashboard renders with sentence-case greeting and Asia/Manila date', function () {
    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    // Sentence case greeting with first name
    $response->assertSee('Welcome back, <span class="text-[#0B5694] dark:text-blue-400">Raniel</span>', false);
    // Not all-caps greeting
    $response->assertDontSee('WELCOME BACK, RANIEL');

    $expectedDate = now()->timezone('Asia/Manila')->format('l, F j');
    $response->assertSee($expectedDate);
});

test('step 0 discrepancy: honest empty state explains when hours exist without shift logs', function () {
    $this->actingAs($this->internUser);

    // Intern has 120.0 hours_rendered, but 0 logs in attendances table
    expect($this->internDetail->hours_rendered)->toBe(120.0);
    expect(Attendance::where('intern_id', $this->internUser->id)->count())->toBe(0);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('No attendance recorded yet');
    $response->assertSee('Your cumulative total of 120 hours includes hours credited directly to your profile or completed assignments without individual shift logs.');
});

test('zero hours empty state is displayed when hours == 0 and logs == 0', function () {
    $this->internDetail->update(['hours_rendered' => 0.0]);

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('No attendance recorded yet');
    $response->assertSee('Your daily shift logs will appear here after your first time in.');
});

test('attendance card reflects not_timed_in state on normal workdays', function () {
    // Freeze time to a weekday (Monday at 9:00 AM)
    Carbon::setTestNow(Carbon::parse('2026-10-05 09:00:00', 'Asia/Manila'));

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee("You haven&#039;t timed in today", false);
    $response->assertSee('Not timed in');
    $response->assertSee('Time in');
});

test('attendance card reflects unavailable state on weekends', function () {
    // Freeze time to a Saturday
    Carbon::setTestNow(Carbon::parse('2026-10-03 10:00:00', 'Asia/Manila'));

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Attendance unavailable');
    $response->assertSee('Weekends are non-working days by default');
    $response->assertSee('Time in unavailable');
});

test('attendance card reflects timed_in state with live timer attributes and time out button', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-05 10:30:00', 'Asia/Manila'));

    $clockInTime = Carbon::parse('2026-10-05 08:00:00', 'Asia/Manila');
    Attendance::create([
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'clock_in' => $clockInTime,
        'clock_out' => null,
        'latitude' => 14.6507,
        'longitude' => 121.0483,
        'status' => 'on_time',
    ]);

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Timed in at');
    $response->assertSee('Active shift');
    $response->assertSee('x-data="attendanceTimer(', false);
    $response->assertSee('clockInEpochMs:', false);
});

test('attendance card reflects day_complete state when already timed out', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-05 18:00:00', 'Asia/Manila'));

    $clockInTime = Carbon::parse('2026-10-05 08:00:00', 'Asia/Manila');
    $clockOutTime = Carbon::parse('2026-10-05 17:00:00', 'Asia/Manila'); // 9 hours - 1hr lunch = 8h 00m
    Attendance::create([
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'clock_in' => $clockInTime,
        'clock_out' => $clockOutTime,
        'latitude' => 14.6507,
        'longitude' => 121.0483,
        'status' => 'on_time',
    ]);

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Day complete');
    $response->assertSee('Today: 8h 00m');
});

test('progress card calculates completion percentage, milestones, and weekly hours', function () {
    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    // 120 / 480 = 25%
    $response->assertSee('120');
    $response->assertSee('HRS');
    $response->assertSee('Total hours rendered');
    $response->assertSee('360');
    $response->assertSee('To Go');
    $response->assertSee('Remaining to reach target');
    $response->assertSee('25% complete');
    $response->assertSee('role="progressbar"', false);
    $response->assertSee('aria-valuenow="25"', false);
    $response->assertSee('0%');
    $response->assertSee('25%');
    $response->assertSee('50%');
    $response->assertSee('75%');
    $response->assertSee('100%');
    $response->assertSee('This week:');
});

test('progress card clamps at 100% and displays certificate link when complete', function () {
    $this->internDetail->update(['hours_rendered' => 500.0]); // Exceeded 480

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Requirement complete · View Certificate');
    $response->assertSee('100% complete');
    $response->assertSee('aria-valuenow="100"', false);
    $response->assertSee(route('intern.certificates.index'));
});

test('tasks are categorized into tabs with overdue tasks highlighted', function () {
    $taskOverdue = Task::create([
        'supervisor_id' => $this->supervisorUser->id,
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'title' => 'Urgent Server Patch',
        'description' => 'Apply security patch',
        'due_date' => now()->subDay(),
        'status' => TaskStatus::PENDING,
        'is_file_required' => false,
    ]);

    $taskUpcoming = Task::create([
        'supervisor_id' => $this->supervisorUser->id,
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'title' => 'Documentation Review',
        'description' => 'Review the user guide',
        'due_date' => now()->addDays(3),
        'status' => TaskStatus::PENDING,
        'is_file_required' => false,
    ]);

    $taskCompleted = Task::create([
        'supervisor_id' => $this->supervisorUser->id,
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'title' => 'Completed Geofence Setup',
        'description' => 'Calibrate GPS',
        'due_date' => now()->subDays(5),
        'status' => TaskStatus::COMPLETED,
        'is_file_required' => false,
    ]);

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Urgent Server Patch');
    $response->assertSee('Overdue');
    $response->assertSee('Documentation Review');
    $response->assertSee('Completed Geofence Setup');
    $response->assertSee('Verified');
});

test('office announcements are displayed with safe text escaping', function () {
    Announcement::create([
        'user_id' => $this->supervisorUser->id,
        'office_id' => $this->office->id,
        'title' => 'Team All-Hands Meeting',
        'content' => 'Quarterly review in Conference Room A.',
    ]);

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Team All-Hands Meeting');
    $response->assertSee('Quarterly review in Conference Room A.');
});

test('recent attendance table renders when attendance logs exist', function () {
    $clockInTime = Carbon::parse('2026-10-01 08:00:00', 'Asia/Manila');
    $clockOutTime = Carbon::parse('2026-10-01 17:00:00', 'Asia/Manila');
    Attendance::create([
        'intern_id' => $this->internUser->id,
        'office_id' => $this->office->id,
        'clock_in' => $clockInTime,
        'clock_out' => $clockOutTime,
        'latitude' => 14.6507,
        'longitude' => 121.0483,
        'status' => 'on_time',
    ]);

    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Oct 1, 2026');
    $response->assertSee('8:00 AM');
    $response->assertSee('5:00 PM');
    $response->assertSee('8h 00m');
    $response->assertSee('on time');
});

test('hard rules: page contains no stat tile row and view defines no font families', function () {
    $this->actingAs($this->internUser);

    $response = $this->get(route('intern.dashboard'));

    $response->assertSuccessful();
    $content = $response->getContent();

    // No stat tiles row (e.g. grid-cols-4 or similar metric stat rows)
    expect($content)->not->toContain('stat-tile');
    expect($content)->not->toContain('stat-card');

    // Verify blade view file does not declare new font-family or font imports
    $bladeSource = file_get_contents(resource_path('views/intern/dashboard.blade.php'));
    expect($bladeSource)->not->toContain('font-family');
    expect($bladeSource)->not->toContain('@font-face');
    expect($bladeSource)->not->toContain('fonts.googleapis.com');
});
