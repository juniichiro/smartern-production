<?php

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\ScheduleMonthBuilder;
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
        'email' => 'intern@smartern.com',
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

    $this->attendanceService = app(AttendanceService::class);
    $this->builder = new ScheduleMonthBuilder();
});

afterEach(function () {
    Carbon::setTestNow();
});

test('param validation handles valid and invalid inputs with safe fallbacks', function () {
    $now = Carbon::parse('2026-10-13 12:00:00', 'Asia/Manila');

    // 1. Valid inputs
    $res = ScheduleMonthBuilder::validateParams('2026-10', 'agenda', '2026-10-15', $now);
    expect($res['month'])->toBe('2026-10')
        ->and($res['view'])->toBe('agenda')
        ->and($res['date'])->toBe('2026-10-15');

    // 2. Invalid month format falls back to current month
    $res = ScheduleMonthBuilder::validateParams('invalid-month', 'month', '2026-10-15', $now);
    expect($res['month'])->toBe('2026-10')
        ->and($res['date'])->toBe('2026-10-15');

    // 3. Out-of-range month (> 5 years away) falls back
    $res = ScheduleMonthBuilder::validateParams('2099-01', 'month', null, $now);
    expect($res['month'])->toBe('2026-10')
        ->and($res['date'])->toBe('2026-10-13');

    // 4. Invalid view falls back to 'month'
    $res = ScheduleMonthBuilder::validateParams('2026-10', 'invalid_view', '2026-10-13', $now);
    expect($res['view'])->toBe('month');

    // 5. Invalid date (e.g. Feb 30) falls back to 1st of month (or today if in month)
    $res = ScheduleMonthBuilder::validateParams('2026-02', 'month', '2026-02-30', $now);
    expect($res['month'])->toBe('2026-02')
        ->and($res['date'])->toBe('2026-02-01');

    // 6. Date mismatching the month falls back
    $res = ScheduleMonthBuilder::validateParams('2026-11', 'month', '2026-10-15', $now);
    expect($res['month'])->toBe('2026-11')
        ->and($res['date'])->toBe('2026-11-01');
});

test('month starting on Sunday: March 2026 has Sunday as first day with zero leading days', function () {
    $now = Carbon::parse('2026-03-10 10:00:00', 'Asia/Manila');
    $data = $this->builder->build($this->internUser, '2026-03', 'month', '2026-03-01', $this->attendanceService, $now);

    expect($data['weeks'])->not->toBeEmpty();
    // First cell of first week must be March 1, 2026
    $firstDay = $data['weeks'][0][0];
    expect($firstDay['date'])->toBe('2026-03-01')
        ->and($firstDay['weekday_name'])->toBe('Sun')
        ->and($firstDay['is_current_month'])->toBeTrue();
});

test('month starting on Saturday: August 2026 has 6 leading days from July', function () {
    $now = Carbon::parse('2026-08-10 10:00:00', 'Asia/Manila');
    $data = $this->builder->build($this->internUser, '2026-08', 'month', '2026-08-01', $this->attendanceService, $now);

    // August 1, 2026 is Saturday, so leading days from July are Sun Jul 26 .. Fri Jul 31 (6 days)
    $firstWeek = $data['weeks'][0];
    expect($firstWeek[0]['date'])->toBe('2026-07-26')
        ->and($firstWeek[0]['is_current_month'])->toBeFalse()
        ->and($firstWeek[6]['date'])->toBe('2026-08-01')
        ->and($firstWeek[6]['is_current_month'])->toBeTrue();
});

test('28-day February in 2026 starts on Sunday and spans 4 full weeks', function () {
    $now = Carbon::parse('2026-02-15 10:00:00', 'Asia/Manila');
    $data = $this->builder->build($this->internUser, '2026-02', 'month', '2026-02-01', $this->attendanceService, $now);

    expect(count($data['weeks']))->toBe(4);
    $firstCell = $data['weeks'][0][0];
    $lastCell = $data['weeks'][3][6];
    expect($firstCell['date'])->toBe('2026-02-01')
        ->and($lastCell['date'])->toBe('2026-02-28');
});

test('leap-year February in 2028 has 29 days and spans 5 weeks', function () {
    $now = Carbon::parse('2028-02-10 10:00:00', 'Asia/Manila');
    $data = $this->builder->build($this->internUser, '2028-02', 'month', '2028-02-01', $this->attendanceService, $now);

    expect(count($data['weeks']))->toBe(5);
    // Feb 29, 2028 is Tuesday
    expect($data['daysByDate']['2028-02-29']['is_current_month'])->toBeTrue();
});

test('31-day month starting on Saturday needs six weeks: August 2026', function () {
    $now = Carbon::parse('2026-08-15 10:00:00', 'Asia/Manila');
    $data = $this->builder->build($this->internUser, '2026-08', 'month', '2026-08-15', $this->attendanceService, $now);

    expect(count($data['weeks']))->toBe(6);
    // Aug 31 is in week 6 (Monday)
    expect($data['weeks'][5][1]['date'])->toBe('2026-08-31')
        ->and($data['weeks'][5][1]['is_current_month'])->toBeTrue();
});

test('navigation across year boundary: December to January and January to December', function () {
    $now = Carbon::parse('2026-12-15 10:00:00', 'Asia/Manila');
    $decData = $this->builder->build($this->internUser, '2026-12', 'month', '2026-12-15', $this->attendanceService, $now);

    expect($decData['prevMonthStr'])->toBe('2026-11')
        ->and($decData['nextMonthStr'])->toBe('2027-01');

    $janData = $this->builder->build($this->internUser, '2027-01', 'month', '2027-01-01', $this->attendanceService, $now);
    expect($janData['prevMonthStr'])->toBe('2026-12')
        ->and($janData['nextMonthStr'])->toBe('2027-02');
});

test('today around midnight in Asia/Manila is correctly determined even if server is UTC', function () {
    // When UTC is 2026-10-13 16:30:00, in Asia/Manila (UTC+8) it is 2026-10-14 00:30:00
    $utcNow = Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-13 16:30:00', 'UTC');
    
    $data = $this->builder->build($this->internUser, '2026-10', 'month', null, $this->attendanceService, $utcNow);

    expect($data['todayDateStr'])->toBe('2026-10-14')
        ->and($data['daysByDate']['2026-10-14']['is_today'])->toBeTrue()
        ->and($data['daysByDate']['2026-10-13']['is_today'])->toBeFalse();
});
