<?php

use App\Http\Controllers\NotificationController;
use App\Mail\GenericExternalNotificationMail;
use App\Mail\OfficeAnnouncementMail;
use App\Mail\OnboardingApplicationReceivedMail;
use App\Mail\SecurityAlertMail;
use App\Mail\TaskNotificationMail;
use App\Mail\UserAccountCreatedMail;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\InternDetail;
use App\Models\Notification;
use App\Models\Office;
use App\Models\Role;
use App\Models\SupervisorDetail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    DB::table('roles')->insertOrIgnore([
        ['id' => 1, 'name' => 'admin'],
        ['id' => 3, 'name' => 'supervisor'],
        ['id' => 5, 'name' => 'intern'],
    ]);

    Office::firstOrCreate(['id' => 1], [
        'name' => 'ICT',
        'latitude' => 14.65,
        'longitude' => 121.05,
        'radius_meters' => 100,
    ]);
});

test('onboarding a user manually sends credentials email', function () {
    $admin = User::factory()->create(['role_id' => 1]);

    $response = $this->actingAs($admin)->post(route('admin.account.store'), [
        'email' => 'newintern@example.com',
        'role_id' => 5,
        'first_name' => 'New',
        'last_name' => 'Intern',
        'school' => 'Quezon City School',
        'course' => 'IT',
        'gender' => 'male',
        'phone_number' => '09171234567',
        'office_id' => 1,
    ]);

    $response->assertRedirect();

    Mail::assertQueued(UserAccountCreatedMail::class, function ($mail) {
        return $mail->hasTo('newintern@example.com') && ! empty($mail->plainPassword);
    });
});

test('supervisor announcement distributes emails to department interns', function () {
    $supervisorUser = User::factory()->create(['role_id' => 3]);
    SupervisorDetail::create([
        'user_id' => $supervisorUser->id,
        'office_id' => 1,
        'first_name' => 'Super',
        'last_name' => 'Visor',
        'phone_number' => '09000000000',
    ]);

    $internUser = User::factory()->create(['role_id' => 5]);
    InternDetail::create([
        'user_id' => $internUser->id,
        'office_id' => 1,
        'first_name' => 'Intern',
        'last_name' => 'One',
        'course' => 'IT',
        'school' => 'SDO',
        'gender' => 'male',
        'phone_number' => '09000000000',
    ]);

    $response = $this->actingAs($supervisorUser)->post(route('supervisor.announcements.store'), [
        'title' => 'Important Meeting',
        'content' => '<p>Please attend the department meeting today at 2 PM.</p>',
    ]);

    $response->assertRedirect();

    Mail::assertQueued(OfficeAnnouncementMail::class, function ($mail) use ($internUser) {
        return $mail->hasTo($internUser->email) && $mail->announcement->title === 'Important Meeting';
    });
});

test('supervisor announcement update distributes emails to department interns', function () {
    $supervisorUser = User::factory()->create(['role_id' => 3]);
    SupervisorDetail::create([
        'user_id' => $supervisorUser->id,
        'office_id' => 1,
        'first_name' => 'Super',
        'last_name' => 'Visor',
        'phone_number' => '09000000000',
    ]);

    $internUser = User::factory()->create(['role_id' => 5]);
    InternDetail::create([
        'user_id' => $internUser->id,
        'office_id' => 1,
        'first_name' => 'Intern',
        'last_name' => 'One',
        'course' => 'IT',
        'school' => 'SDO',
        'gender' => 'male',
        'phone_number' => '09000000000',
    ]);

    $announcement = Announcement::create([
        'user_id' => $supervisorUser->id,
        'title' => 'Important Meeting',
        'content' => '<p>Please attend the department meeting today at 2 PM.</p>',
    ]);

    // Reset mail queue assertions
    Mail::fake();

    $response = $this->actingAs($supervisorUser)->put(route('supervisor.announcements.update', $announcement), [
        'title' => 'Updated Meeting Time',
        'content' => '<p>Please attend the department meeting today at 3 PM.</p>',
    ]);

    $response->assertRedirect();

    Mail::assertQueued(OfficeAnnouncementMail::class, function ($mail) use ($internUser) {
        return $mail->hasTo($internUser->email) && $mail->announcement->title === 'Updated Meeting Time';
    });
});

test('external notification controller trigger sends mailable', function () {
    $internUser = User::factory()->create(['role_id' => 5]);

    NotificationController::send(
        $internUser->id,
        'System Update',
        'system',
        'Your profile has been approved.',
        null,
        true // isExternal
    );

    Mail::assertQueued(GenericExternalNotificationMail::class, function ($mail) use ($internUser) {
        return $mail->hasTo($internUser->email) && $mail->notification->type === 'System Update';
    });
});

test('security alert mail is sent correctly', function () {
    $admin = User::factory()->create(['role_id' => 1]);
    $intern = User::factory()->create(['role_id' => 5]);

    $log = AuditLog::create([
        'user_id' => $intern->id,
        'action' => 'Sensitive Data Access',
        'resource' => 'System',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
        'anomaly_score' => 0.85,
        'timestamp' => now(),
    ]);

    Mail::to($admin->email)->send(new SecurityAlertMail($log, $admin));

    Mail::assertQueued(SecurityAlertMail::class, function ($mail) use ($admin, $log) {
        return $mail->hasTo($admin->email) && $mail->log->log_id === $log->log_id;
    });
});

test('office announcement mail does not render image attachment inline and has no mail attachments', function () {
    $supervisorUser = User::factory()->create(['role_id' => 3]);
    $internUser = User::factory()->create(['role_id' => 5]);

    $announcement = Announcement::create([
        'user_id' => $supervisorUser->id,
        'title' => 'Important Image Announcement',
        'content' => 'Please view the attached flyer.',
        'attachment' => 'announcement/test_image.png',
    ]);

    $mailable = new OfficeAnnouncementMail($announcement, $internUser);

    // Assert no attachments are attached to the email itself
    expect($mailable->attachments())->toBeEmpty();

    // Render the mailable to get the HTML content
    $html = $mailable->render();

    // Check that it contains the content but not any embedded image
    expect($html)->toContain('Please view the attached flyer.');
    expect($html)->not->toContain('alt="Announcement Image"');
    expect($html)->not->toContain('test_image.png');
});

test('office announcement mail does not render file attachment card and has no mail attachments', function () {
    $supervisorUser = User::factory()->create(['role_id' => 3]);
    $internUser = User::factory()->create(['role_id' => 5]);

    $announcement = Announcement::create([
        'user_id' => $supervisorUser->id,
        'title' => 'Important PDF Announcement',
        'content' => 'Please view the attached document.',
        'attachment' => 'announcement/test_doc.pdf',
    ]);

    $mailable = new OfficeAnnouncementMail($announcement, $internUser);

    // Assert no attachments are attached to the email itself
    expect($mailable->attachments())->toBeEmpty();

    // Render the mailable to get the HTML content
    $html = $mailable->render();

    // Check that it contains the content but no file download/preview card
    expect($html)->toContain('Please view the attached document.');
    expect($html)->not->toContain('Attachment Included');
    expect($html)->not->toContain('test_doc.pdf');
    expect($html)->not->toContain('Preview Attachment');
});

test('task approved email notification renders correctly with Canvas LMS style and remarks', function () {
    $supervisor = User::factory()->create(['role_id' => 3]);
    $internUser = User::factory()->create(['role_id' => 5]);

    $task = Task::create([
        'supervisor_id' => $supervisor->id,
        'intern_id' => $internUser->id,
        'office_id' => 1,
        'title' => 'Important Coding Assignment',
        'description' => 'Write a PHP script.',
        'remarks' => 'Superb solution, well done!',
        'status' => 'completed',
    ]);

    $notification = Notification::create([
        'recipient_id' => $internUser->id,
        'sender_id' => $supervisor->id,
        'type' => 'Task Approved',
        'category' => 'task',
        'message' => "Your submission for '{$task->title}' has been reviewed.",
        'target_url' => 'http://localhost/tasks/'.$task->id,
        'is_read' => false,
        'is_external' => true,
    ]);

    $mailable = new TaskNotificationMail($task, $notification);

    // Check envelope subject format
    expect($mailable->envelope()->subject)->toBe('Task Approved: Important Coding Assignment');

    // Render HTML content
    $html = $mailable->render();

    // Verify layout matches Canvas LMS grading format
    expect($html)->toContain('Your assignment <strong>Important Coding Assignment</strong> has been approved.');
    expect($html)->toContain('approved:');
    expect($html)->toContain('remarks:');
    expect($html)->toContain('Superb solution, well done!');
    expect($html)->toContain('View assignment');
    expect($html)->toContain('Update your notification settings');
});

test('task revision requested email notification renders correctly with Canvas LMS style and remarks', function () {
    $supervisor = User::factory()->create(['role_id' => 3]);
    $internUser = User::factory()->create(['role_id' => 5]);

    $task = Task::create([
        'supervisor_id' => $supervisor->id,
        'intern_id' => $internUser->id,
        'office_id' => 1,
        'title' => 'Math Assignment',
        'description' => 'Solve equations.',
        'remarks' => 'Please fix equation 3 and re-submit.',
        'status' => 'revision',
        'due_date' => now()->addDays(3),
    ]);

    $notification = Notification::create([
        'recipient_id' => $internUser->id,
        'sender_id' => $supervisor->id,
        'type' => 'Revision Requested',
        'category' => 'task',
        'message' => "A revision has been requested for '{$task->title}'.",
        'target_url' => 'http://localhost/tasks/'.$task->id,
        'is_read' => false,
        'is_external' => true,
    ]);

    // Test case 1: due date not modified
    $mailable = new TaskNotificationMail($task, $notification);
    $html = $mailable->render();
    expect($mailable->envelope()->subject)->toBe('Revision Requested: Math Assignment');
    expect($html)->toContain('Your assignment <strong>Math Assignment</strong> has been revised.');
    expect($html)->not->toContain('new due date:');

    // Test case 2: due date modified
    $mailableModified = new TaskNotificationMail($task, $notification, [
        'due_date_modified' => true,
    ]);
    $htmlModified = $mailableModified->render();
    expect($htmlModified)->toContain('new due date:');
    expect($htmlModified)->toContain($task->due_date->format('M d \a\t g:ia'));
});

test('task assignment email notification renders correctly with Canvas LMS style', function () {
    $supervisor = User::factory()->create(['role_id' => 3]);
    $internUser = User::factory()->create(['role_id' => 5]);

    $task = Task::create([
        'supervisor_id' => $supervisor->id,
        'intern_id' => $internUser->id,
        'office_id' => 1,
        'title' => 'New Project Proposal',
        'description' => 'Write a proposal.',
        'due_date' => now()->addDays(2),
        'status' => 'pending',
    ]);

    $notification = Notification::create([
        'recipient_id' => $internUser->id,
        'sender_id' => $supervisor->id,
        'type' => 'Task Assignment',
        'category' => 'task',
        'message' => "You have been assigned a new task: '{$task->title}'.",
        'target_url' => 'http://localhost/tasks/'.$task->id,
        'is_read' => false,
        'is_external' => true,
    ]);

    $mailable = new TaskNotificationMail($task, $notification);

    // Check envelope subject format
    expect($mailable->envelope()->subject)->toBe('Task Assignment: New Project Proposal');

    // Render HTML content
    $html = $mailable->render();

    // Verify layout matches Canvas LMS assignment format
    expect($html)->toContain('You have been assigned a new task: <strong>New Project Proposal</strong>.');
    expect($html)->toContain('due:');
    expect($html)->toContain('View assignment');
    expect($html)->toContain('Update your notification settings');
});

test('supervisor assigning a task triggers task assignment email notification', function () {
    // Fake the Mail
    Mail::fake();

    // Create supervisor user and detail
    $supervisor = User::factory()->create(['role_id' => 3]);
    SupervisorDetail::create([
        'user_id' => $supervisor->id,
        'office_id' => 1,
        'first_name' => 'Super',
        'last_name' => 'Visor',
        'phone_number' => '09000000000',
    ]);

    // Create intern user and detail
    $internUser = User::factory()->create(['role_id' => 5]);
    $intern = InternDetail::create([
        'user_id' => $internUser->id,
        'office_id' => 1,
        'first_name' => 'Raniel',
        'last_name' => 'Britos',
        'phone_number' => '09171234567',
        'course' => 'IT',
        'school' => 'Quezon City School',
        'gender' => 'male',
    ]);

    // Perform request as supervisor to assign a task
    $response = $this->actingAs($supervisor)->post(route('supervisor.tasks.store'), [
        'intern_id' => $intern->getRouteKey(), // Hashid representing the intern detail record
        'title' => 'Urgent Security Audit',
        'description' => 'Audit the system.',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'is_file_required' => false,
    ]);

    // Assert redirect
    $response->assertRedirect();

    // Assert that the email is queued/sent
    Mail::assertQueued(TaskNotificationMail::class, function ($mail) use ($internUser) {
        return $mail->hasTo($internUser->email) && $mail->notification->type === 'Task Assignment';
    });
});

test('supervisor approving a task triggers task approved email notification', function () {
    // Fake the Mail
    Mail::fake();

    // Create supervisor user and detail
    $supervisor = User::factory()->create(['role_id' => 3]);
    SupervisorDetail::create([
        'user_id' => $supervisor->id,
        'office_id' => 1,
        'first_name' => 'Super',
        'last_name' => 'Visor',
        'phone_number' => '09000000000',
    ]);

    // Create intern user and detail
    $internUser = User::factory()->create(['role_id' => 5]);
    $intern = InternDetail::create([
        'user_id' => $internUser->id,
        'office_id' => 1,
        'first_name' => 'Raniel',
        'last_name' => 'Britos',
        'phone_number' => '09171234567',
        'course' => 'IT',
        'school' => 'Quezon City School',
        'gender' => 'male',
    ]);

    // Create a task
    $task = Task::create([
        'supervisor_id' => $supervisor->id,
        'intern_id' => $internUser->id,
        'office_id' => 1,
        'title' => 'Urgent Security Audit',
        'description' => 'Audit the system.',
        'status' => 'pending',
    ]);

    // Perform request as supervisor to approve the task
    $response = $this->actingAs($supervisor)->patch(route('supervisor.tasks.update-status', $task), [
        'status' => 'completed',
        'remarks' => 'Approved',
    ]);

    // Assert redirect
    $response->assertRedirect();

    // Assert that the email is queued/sent
    Mail::assertQueued(TaskNotificationMail::class, function ($mail) use ($internUser) {
        return $mail->hasTo($internUser->email) && $mail->notification->type === 'Task Approved';
    });
});

test('supervisor requesting task revision triggers task revision email notification', function () {
    // Fake the Mail
    Mail::fake();

    // Create supervisor user and detail
    $supervisor = User::factory()->create(['role_id' => 3]);
    SupervisorDetail::create([
        'user_id' => $supervisor->id,
        'office_id' => 1,
        'first_name' => 'Super',
        'last_name' => 'Visor',
        'phone_number' => '09000000000',
    ]);

    // Create intern user and detail
    $internUser = User::factory()->create(['role_id' => 5]);
    $intern = InternDetail::create([
        'user_id' => $internUser->id,
        'office_id' => 1,
        'first_name' => 'Raniel',
        'last_name' => 'Britos',
        'phone_number' => '09171234567',
        'course' => 'IT',
        'school' => 'Quezon City School',
        'gender' => 'male',
    ]);

    // Create a task
    $task = Task::create([
        'supervisor_id' => $supervisor->id,
        'intern_id' => $internUser->id,
        'office_id' => 1,
        'title' => 'Urgent Security Audit',
        'description' => 'Audit the system.',
        'status' => 'pending',
    ]);

    // Perform request as supervisor to request revision
    $response = $this->actingAs($supervisor)->patch(route('supervisor.tasks.update-status', $task), [
        'status' => 'revision',
        'remarks' => 'Please add more details',
    ]);

    // Assert redirect
    $response->assertRedirect();

    // Assert that the email is queued/sent
    Mail::assertQueued(TaskNotificationMail::class, function ($mail) use ($internUser) {
        return $mail->hasTo($internUser->email) && $mail->notification->type === 'Revision Requested';
    });
});

test('intern submitting a task triggers intern submission email notification to supervisor', function () {
    // Fake the Mail
    Mail::fake();

    // Create supervisor user and detail
    $supervisor = User::factory()->create(['role_id' => 3]);
    SupervisorDetail::create([
        'user_id' => $supervisor->id,
        'office_id' => 1,
        'first_name' => 'Super',
        'last_name' => 'Visor',
        'phone_number' => '09000000000',
    ]);

    // Create intern user and detail
    $internUser = User::factory()->create(['role_id' => 5]);
    $intern = InternDetail::create([
        'user_id' => $internUser->id,
        'office_id' => 1,
        'first_name' => 'Raniel',
        'last_name' => 'Britos',
        'phone_number' => '09171234567',
        'course' => 'IT',
        'school' => 'Quezon City School',
        'gender' => 'male',
    ]);

    // Create a task assigned to this intern with some previous supervisor remarks
    $task = Task::create([
        'supervisor_id' => $supervisor->id,
        'intern_id' => $internUser->id,
        'office_id' => 1,
        'title' => 'Urgent Security Audit',
        'description' => 'Audit the system.',
        'status' => 'pending',
        'is_file_required' => false,
        'remarks' => 'Please add more detailed explanation.',
    ]);

    // Perform request as intern to submit the task
    $response = $this->actingAs($internUser)->post(route('intern.tasks.submit', $task), [
        'content' => 'Here is my submission content.',
    ]);

    // Assert redirect and no validation errors
    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    // Assert that the previous supervisor remarks are cleared
    expect($task->fresh()->remarks)->toBeNull();

    // Assert that the email is queued/sent to supervisor
    Mail::assertQueued(TaskNotificationMail::class, function ($mail) use ($supervisor) {
        return $mail->hasTo($supervisor->email) && $mail->notification->type === 'Intern Submission';
    });
});

test('submitting onboarding application triggers email notification to the applicant', function () {
    // Fake the Mail
    Mail::fake();

    // Mock role creation
    Role::firstOrCreate(['id' => 2], ['name' => 'head_supervisor']);

    $pdf = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

    $response = $this->post(route('onboarding.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'school' => 'Quezon City University',
        'gender' => 'male',
        'phone_number' => '09123456789',
        'emergency_contact' => 'Jane Doe',
        'emergency_contact_number' => '09123456789',
        'resume_pdf' => $pdf,
        'tech_skills' => ['PHP'],
        'tech_scores' => [5],
        'privacy_acknowledgement' => 1,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    Mail::assertQueued(OnboardingApplicationReceivedMail::class, function ($mail) {
        return $mail->hasTo('johndoe@example.com') && $mail->applicant->first_name === 'John';
    });
});
