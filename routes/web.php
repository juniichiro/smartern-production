<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Auth\PasswordUpdateController;
use App\Http\Controllers\Coordinator\DashboardController as CoordinatorDashboard;
use App\Http\Controllers\Coordinator\InternController as CoordinatorIntern;
use App\Http\Controllers\HeadSupervisor\AnalyticsController;
use App\Http\Controllers\HeadSupervisor\AssessmentItemController;
use App\Http\Controllers\HeadSupervisor\CertificateController as HeadSupervisorCertificate;
use App\Http\Controllers\HeadSupervisor\DashboardController as HeadSupervisorDashboard;
use App\Http\Controllers\HeadSupervisor\InternManagementController;
use App\Http\Controllers\HeadSupervisor\OfficeManagementController;
use App\Http\Controllers\HeadSupervisor\OfficeMatchingController;
use App\Http\Controllers\HeadSupervisor\OnboardingManagementController;
use App\Http\Controllers\HeadSupervisor\ScheduleController;
use App\Http\Controllers\Intern\AnnouncementController as InternAnnouncement;
use App\Http\Controllers\Intern\AttendanceController as InternAttendance;
use App\Http\Controllers\Intern\CertificateController as InternCertificate;
use App\Http\Controllers\Intern\DashboardController as InternDashboard;
use App\Http\Controllers\Intern\ProfileController as InternProfile;
use App\Http\Controllers\Intern\ProgressController;
use App\Http\Controllers\MFAController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Supervisor\AnnouncementController as SupervisorAnnouncement;
use App\Http\Controllers\Supervisor\AttendanceController as SupervisorAttendance;
use App\Http\Controllers\Supervisor\CertificateController as SupervisorCertificate;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboard;
use App\Http\Controllers\Supervisor\OfficeManagementController as SupervisorIntern;
use App\Http\Controllers\Supervisor\ReportController as SupervisorReport;
use App\Http\Controllers\Supervisor\TaskController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// Public Onboarding Microsite
Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
Route::post('/onboarding', [OnboardingController::class, 'store'])->middleware('throttle:5,1')->name('onboarding.store');
Route::post('/onboarding/parse-resume', [OnboardingController::class, 'parseResume'])->middleware('throttle:5,1')->name('onboarding.parse');
Route::get('/onboarding/universities', [OnboardingController::class, 'getUniversities'])->name('onboarding.universities');
Route::post('/onboarding/check-rejection', [OnboardingController::class, 'checkRejection'])->name('onboarding.check-rejection');
Route::get('/privacy-policy', [OnboardingController::class, 'privacyPolicy'])->name('privacy-policy');

// Public QR Scan Routes
Route::get('/attendance/verify', [InternAttendance::class, 'showVerifyPage'])->name('attendance.verify');
Route::post('/attendance/process', [InternAttendance::class, 'processScan'])->name('attendance.process');
Route::get('/mfa/scan', [MFAController::class, 'scanCode'])->name('mfa.scan');

Route::get('/dashboard', function () {
    $user = Auth::user();
    $role = strtolower($user->role->name);

    $destination = match ($role) {
        'admin' => route('admin.dashboard'),
        'super_admin' => route('admin.dashboard'),
        'head_supervisor' => route('head-supervisor.dashboard'),
        'supervisor' => route('supervisor.dashboard'),
        'intern' => route('intern.dashboard'),
        'school_coordinator' => route('coordinator.dashboard'),
        default => url('/'),
    };

    return redirect($destination);

})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/session/ping', fn () => response()->json(['status' => 'ok']))->name('session.ping');

    Route::post('/security/run-analysis', [SecurityController::class, 'runAnalysis'])->name('security.run');

    Route::get('/submissions/{submission}/file', [App\Http\Controllers\SubmissionController::class, 'viewFile'])->name('submissions.file');

    Route::get('profile/change-password', [PasswordUpdateController::class, 'edit'])
        ->name('profile.change-password');

    Route::put('profile/change-password', [PasswordUpdateController::class, 'update'])
        ->name('profile.password.update');

    Route::post('profile/request-password-reset', [PasswordUpdateController::class, 'requestReset'])
        ->name('profile.password.request-reset');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/resolve', [NotificationController::class, 'resolve'])->name('resolve');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('markAllRead');
    });

    // MFA
    Route::get('/mfa/setup', [MFAController::class, 'setup'])->name('mfa.setup');
    Route::post('/mfa/enable', [MFAController::class, 'enable'])->name('mfa.enable');
    Route::post('/mfa/disable', [MFAController::class, 'disable'])->name('mfa.disable');
    Route::get('/mfa/verify', [MFAController::class, 'verifyView'])->name('mfa.verify');
    Route::post('/mfa/verify', [MFAController::class, 'verify'])->name('mfa.verify.post');
    Route::post('/mfa/send-email-code', [MFAController::class, 'sendEmailCode'])->name('mfa.send-email-code');
    Route::get('/mfa/setup-data', [MFAController::class, 'getSetupData'])->name('mfa.setup-data');
    Route::post('/mfa/enable-totp-ajax', [MFAController::class, 'enableTotpAjax'])->name('mfa.enable-totp-ajax');
    Route::post('/mfa/disable-totp-ajax', [MFAController::class, 'disableTotpAjax'])->name('mfa.disable-totp-ajax');
    Route::post('/mfa/enable-email-ajax', [MFAController::class, 'enableEmailAjax'])->name('mfa.enable-email-ajax');
    Route::post('/mfa/disable-mfa-ajax', [MFAController::class, 'disableMfaAjax'])->name('mfa.disable-mfa-ajax');

    // ADMIN (Role Restricted)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/accounts', [AccountController::class, 'index'])->name('account');
        Route::get('/accounts/template', [AccountController::class, 'downloadTemplate'])->name('accounts.template');
        Route::post('/accounts/import', [AccountController::class, 'importCsv'])->name('accounts.import');
        Route::post('/accounts/onboarding-approve/{id}', [AccountController::class, 'approveOnboardingAccount'])->name('accounts.onboarding-approve');
        Route::put('/accounts/onboarding-update/{id}', [AccountController::class, 'updateOnboardingAccount'])->name('accounts.onboarding-update');
        Route::post('/users', [AccountController::class, 'store'])->name('account.store');
        Route::put('/accounts/{user}', [AccountController::class, 'update'])->name('account.update');
        Route::delete('/accounts/{user}', [AccountController::class, 'destroy'])->name('account.destroy');
        Route::get('/security', [SecurityController::class, 'index'])->name('security');
        Route::get('/security/export', [SecurityController::class, 'export'])->name('security.export');
        Route::get('/security/guide', [SecurityController::class, 'downloadGuide'])->name('security.guide');
        Route::post('/security/mark-safe/{user}/{log}', [SecurityController::class, 'markSafe'])->name('security.markSafe');
        Route::post('/security/mark-compromised/{user}/{log}', [SecurityController::class, 'markCompromised'])->name('security.markCompromised');
        Route::post('/security/mark-suspended/{user}/{log}', [SecurityController::class, 'markSuspended'])->name('security.markSuspended');

        // System Configuration & Backups
        Route::get('/configuration', [AdminController::class, 'configuration'])->name('configuration');
        Route::get('/offices', [AdminController::class, 'offices'])->name('offices');
        Route::put('/geofence/update', [AdminController::class, 'updateGeofence'])->name('offices.geofence.update');
        Route::get('/geofence/reverse-geocode', [AdminController::class, 'reverseGeocode'])->name('offices.geofence.reverse');
        Route::get('/geofence/search-location', [AdminController::class, 'searchLocation'])->name('offices.geofence.search');
        Route::post('/configuration/backup', [AdminController::class, 'triggerManualBackup'])->name('backup.trigger');
        Route::post('/configuration/backup/upload', [AdminController::class, 'uploadSnapshot'])->name('backup.upload');
        Route::post('/configuration/backup/{backup}/restore', [AdminController::class, 'restoreSnapshot'])->name('backup.restore');
        Route::get('/configuration/backup/{backup}/download', [AdminController::class, 'downloadBackup'])->name('backup.download');
        Route::delete('/configuration/backup/{backup}', [AdminController::class, 'destroyBackup'])->name('backup.destroy');
        Route::get('/docs/{path?}', [\App\Http\Controllers\Admin\DocsController::class, 'index'])->where('path', '.*')->name('docs');
    });

    // SUPERVISOR (Role Restricted)
    Route::middleware(['role:supervisor,head_supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorDashboard::class, 'index'])->name('dashboard');
        Route::get('/interns', [SupervisorIntern::class, 'index'])->name('interns');
        Route::get('/interns/{user}', [SupervisorIntern::class, 'show'])->name('interns.show');
        Route::post('/interns/{user}/extend', [SupervisorIntern::class, 'requestExtension'])->name('interns.extend');
        Route::post('/office/requirements', [SupervisorIntern::class, 'updateRequirements'])->name('office.requirements');
        Route::get('/office/rules', [SupervisorIntern::class, 'editRules'])->name('office.rules');
        Route::put('/office/rules', [SupervisorIntern::class, 'updateRules'])->name('office.rules.update');
        Route::post('/office/assessment-items', [SupervisorIntern::class, 'storeAssessmentItem'])->name('office.assessment-items.store');
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
        Route::get('/tasks/intern/{user}', [TaskController::class, 'internTasks'])->name('tasks.intern');
        Route::put('/tasks/update/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/delete/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/review/{taskKey}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

        Route::get('/announcements', [SupervisorAnnouncement::class, 'index'])->name('announcements');
        Route::get('/announcements/{announcementKey}', [SupervisorAnnouncement::class, 'show'])->name('announcements.show');
        Route::post('/announcements/store', [SupervisorAnnouncement::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [SupervisorAnnouncement::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [SupervisorAnnouncement::class, 'destroy'])->name('announcements.destroy');

        Route::get('/attendance', [SupervisorAttendance::class, 'index'])->name('attendance');
        Route::get('/reports', [SupervisorReport::class, 'reports'])->name('reports');
        Route::get('/reports/pdf', [SupervisorReport::class, 'exportPDF'])->name('reports.pdf');

        // Supervisor Certificates
        Route::get('/certificates', [SupervisorCertificate::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{certificate}/preview', [SupervisorCertificate::class, 'preview'])->name('certificates.preview');
        Route::post('/certificates/{certificate}/verify', [SupervisorCertificate::class, 'verify'])->name('certificates.verify');
        Route::post('/certificates/{certificate}/endorse', [SupervisorCertificate::class, 'endorse'])->name('certificates.endorse');
        Route::get('/certificates/{certificate}/download', [SupervisorCertificate::class, 'download'])->name('certificates.download');
        Route::get('/schedule', [App\Http\Controllers\Supervisor\ScheduleController::class, 'index'])->name('schedule.index');
        Route::patch('/schedule/absence/{absence}', [App\Http\Controllers\Supervisor\ScheduleController::class, 'reviewAbsenceRequest'])->name('schedule.absence.review');
        Route::get('/schedule/absence/{absence}/attachment', [App\Http\Controllers\Supervisor\ScheduleController::class, 'showAttachment'])->name('schedule.absence.show');
        
        Route::get('/office-matching', [App\Http\Controllers\Supervisor\DashboardController::class, 'officeMatching'])->name('office-matching');
        Route::post('/office-matching/{matchResult}/request', [App\Http\Controllers\Supervisor\DashboardController::class, 'requestIntern'])->name('office-matching.request');
    });

    // HEAD SUPERVISOR (Role Restricted)
    Route::middleware(['role:head_supervisor'])->prefix('head-supervisor')->name('head-supervisor.')->group(function () {
        Route::get('/dashboard', [HeadSupervisorDashboard::class, 'index'])->name('dashboard');

        // Office Management (Onboarding)
        Route::get('/offices', [OfficeManagementController::class, 'index'])->name('offices.index');
        Route::post('/offices', [OfficeManagementController::class, 'store'])->name('offices.store');
        Route::put('/offices/{office}', [OfficeManagementController::class, 'update'])->name('offices.update');
        Route::put('/geofence/update', [OfficeManagementController::class, 'updateGeofence'])->name('offices.geofence.update');
        Route::get('/offices/{office}/rules', [OfficeManagementController::class, 'editRules'])->name('offices.rules');
        Route::put('/offices/{office}/rules', [OfficeManagementController::class, 'updateRules'])->name('offices.rules.update');
        Route::delete('/offices/{office}', [OfficeManagementController::class, 'destroy'])->name('offices.destroy');

        // Onboarding Management
        Route::get('/onboarding', [OnboardingManagementController::class, 'index'])->name('onboarding.index');
        Route::get('/onboarding/export', [OnboardingManagementController::class, 'exportForMatching'])->name('onboarding.export');
        Route::post('/onboarding/{applicant}/reject', [OnboardingManagementController::class, 'reject'])->name('onboarding.reject');

        // Intern Management
        Route::get('/interns', [InternManagementController::class, 'index'])->name('interns');
        Route::get('/interns/{user}', [InternManagementController::class, 'show'])->name('interns.show');
        Route::get('/my-interns', [InternManagementController::class, 'myInterns'])->name('my-interns');
        Route::put('/interns/{user}/schedule', [InternManagementController::class, 'updateSchedule'])->name('interns.update-schedule');
        Route::put('/extensions/{extension}/resolve', [InternManagementController::class, 'resolveExtension'])->name('interns.resolve-extension');

        // Assessment Form Management
        Route::resource('assessment-items', AssessmentItemController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Analytics & Reports
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
        Route::post('/analytics/pdf', [AnalyticsController::class, 'exportPDF'])->name('analytics.pdf');
        Route::get('/analytics/excel', [AnalyticsController::class, 'exportExcel'])->name('analytics.excel');
        Route::get('/analytics/report-excel', [AnalyticsController::class, 'exportReportExcel'])->name('report.export');
        Route::get('/analytics/report-pdf', [AnalyticsController::class, 'exportReportPDF'])->name('report.pdf-export');
        Route::get('/reports', fn () => redirect()->route('head-supervisor.analytics', request()->query()))->name('report');

        // Office Matching Routes[cite: six]
        Route::get('/office-matching', [OfficeMatchingController::class, 'index'])->name('matching');
        Route::get('/office-matching/guide', [OfficeMatchingController::class, 'downloadGuide'])->name('matching.guide');
        Route::get('/office-matching/template', [OfficeMatchingController::class, 'downloadTemplate'])->name('template');
        Route::post('/office-matching/upload', [OfficeMatchingController::class, 'upload'])->name('upload');
        Route::delete('/office-matching/clear', [OfficeMatchingController::class, 'clearResults'])->name('matching.clear');
        Route::post('/office-matching/finalize', [OfficeMatchingController::class, 'finalizeBatch'])->name('matching.finalize');
        Route::post('/office-matching/unfinalize', [OfficeMatchingController::class, 'unfinalize'])->name('matching.unfinalize');
        Route::get('/office-matching/export', [OfficeMatchingController::class, 'exportCsv'])->name('matching.export');
        Route::get('/office-matching/{applicant}/resume', [OfficeMatchingController::class, 'viewResume'])->name('matching.resume');
        Route::post('/office-matching/{applicant}/evaluate', [OfficeMatchingController::class, 'storeEvaluation'])->name('matching.evaluate');

        // Head Supervisor Certificates
        Route::get('/certificates', [HeadSupervisorCertificate::class, 'index'])->name('certificates.index');
        Route::get('/endorsements', [HeadSupervisorCertificate::class, 'endorsements'])->name('certificates.endorsements');
        // Route::get('/certificates-template', [HeadSupervisorCertificate::class, 'editTemplate'])->name('certificates.template.edit');
        // Route::post('/certificates-template', [HeadSupervisorCertificate::class, 'updateTemplate'])->name('certificates.template.update');
        Route::put('/certificates/{certificate}/update-details', [HeadSupervisorCertificate::class, 'updateDetails'])->name('certificates.update-details');
        Route::get('/certificates/{certificate}', [HeadSupervisorCertificate::class, 'show'])->name('certificates.show');
        Route::post('/certificates/{certificate}/publish', [HeadSupervisorCertificate::class, 'publish'])->name('certificates.publish');
        Route::get('/certificates/{certificate}/download', [HeadSupervisorCertificate::class, 'download'])->name('certificates.download');

        // Schedule Overrides
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

        Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::patch('/schedule/absence/{absence}', [ScheduleController::class, 'reviewAbsenceRequest'])->name('schedule.absence.review');
        Route::get('/schedule/absence/{absence}/attachment', [ScheduleController::class, 'showAttachment'])->name('schedule.absence.show');
        Route::put('/schedule/{schedule}', [ScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
    });

    // INTERN (Role Restricted)
    Route::middleware(['role:intern'])->prefix('intern')->name('intern.')->group(function () {
        Route::get('/dashboard', [InternDashboard::class, 'index'])->name('dashboard');
        Route::get('/announcements', [InternAnnouncement::class, 'index'])->name('announcements');
        Route::get('/announcements/{announcementKey}', [InternAnnouncement::class, 'show'])->name('announcements.show');
        Route::get('/attendance', [InternAttendance::class, 'index'])->name('attendance');
        Route::get('/attendance/check-status', [InternAttendance::class, 'checkStatus'])->name('attendance.check-status');
        Route::get('/progress', [ProgressController::class, 'index'])->name('progress');
        Route::get('/progress/journal/preview', [ProgressController::class, 'previewJournal'])->name('journal.preview');
        Route::post('/progress/journal', [ProgressController::class, 'storeJournal'])->name('journal.store');
        Route::put('/progress/journal/{journal}', [ProgressController::class, 'updateJournal'])->name('journal.update');
        Route::get('/progress/export', [ProgressController::class, 'exportAccomplishments'])->name('progress.export');
        Route::get('/tasks/{taskKey}', [ProgressController::class, 'showTask'])->name('tasks.show');
        Route::post('/tasks/{task}/submit', [ProgressController::class, 'submitTask'])->name('tasks.submit');
        Route::get('/certificate', [InternCertificate::class, 'index'])->name('certificates.index');
        Route::get('/certificate/{certificate}/download', [InternCertificate::class, 'download'])->name('certificates.download');
        Route::get('/profile', [InternProfile::class, 'index'])->name('profile');
        Route::post('/profile/avatar', [InternProfile::class, 'updateAvatar'])->name('profile.avatar');
        Route::get('/schedule', [App\Http\Controllers\Intern\ScheduleController::class, 'index'])->name('schedule.index');
        Route::post('/schedule/absence', [App\Http\Controllers\Intern\ScheduleController::class, 'storeAbsenceRequest'])->name('schedule.absence.store');
        Route::post('/schedule/extension', [App\Http\Controllers\Intern\ScheduleController::class, 'storeExtensionRequest'])->name('schedule.extension.store');
        Route::put('/schedule/absence/{absence}', [App\Http\Controllers\Intern\ScheduleController::class, 'updateAbsenceRequest'])->name('schedule.absence.update');
        Route::delete('/schedule/absence/{absence}', [App\Http\Controllers\Intern\ScheduleController::class, 'destroyAbsenceRequest'])->name('schedule.absence.revoke');
        Route::get('/schedule/absence/{absence}/attachment', [App\Http\Controllers\Intern\ScheduleController::class, 'showAttachment'])->name('schedule.absence.show');
    });

    // COORDINATOR (Role Restricted)
    Route::middleware(['role:school_coordinator'])->prefix('coordinator')->name('coordinator.')->group(function () {
        Route::get('/dashboard', [CoordinatorDashboard::class, 'index'])->name('dashboard');
        Route::get('/interns', [CoordinatorIntern::class, 'index'])->name('interns');
        Route::get('/interns/export', [CoordinatorIntern::class, 'exportPDF'])->name('interns.export');
        Route::get('/interns/{user}', [CoordinatorIntern::class, 'show'])->name('interns.show');
        Route::get('/schedule', [App\Http\Controllers\Coordinator\ScheduleController::class, 'index'])->name('schedule.index');
    });

    // PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/privacy/acknowledge', [ProfileController::class, 'acknowledgePrivacy'])->name('privacy.acknowledge');
});

// Document Templates (Accessible without login for locked-out users)
Route::get('/document-templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('/document-templates/download/{type}', [TemplateController::class, 'download'])->name('templates.download');

require __DIR__.'/auth.php';
