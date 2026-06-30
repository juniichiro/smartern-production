# Controllers Catalog Reference

This document provides an exhaustive reference of all controllers inside the `app/Http/Controllers/` namespace.

---

## 1. Authentication Controllers (`App\Http\Controllers\Auth`)

These controllers manage security verification, session creation, and password modification.

### `AuthenticatedSessionController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Routes**: `/login` (GET/POST), `/logout` (POST)
- **Methods**:
  - `create(Request $request)`: Renders the login screen.
  - `store(LoginRequest $request)`: Validates email/password credentials, processes login attempts, and initiates sessions.
  - `destroy(Request $request)`: Invalidates the user session and handles token regeneration.

### `ConfirmablePasswordController`
- **Routes**: `/confirm-password` (GET/POST)
- **Methods**:
  - `show()`: Renders the password confirmation view.
  - `store(Request $request)`: Assures the user enters their correct password before accessing high-security settings.

### `PasswordUpdateController` & `PasswordController`
- **Routes**: `/password` (PUT)
- **Methods**:
  - `update(Request $request)`: Validates strong password rules (12+ characters, symbols, numbers, mixed cases) and updates the database records.

---

## 2. Administrative Controllers (`App\Http\Controllers\Admin`)

These controllers regulate systems, backup archives, geofences, and machine learning outputs.

### `AccountController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Routes**: `/admin/users`, `/admin/users/import` (POST)
- **Methods**:
  - `index()`: Filters and lists user accounts by role (Intern, Supervisor, Coordinator) and office.
  - `store(Request $request)`: Automatically generates a secure password, registers the user, and queues a welcome email.
  - `importBulkCSV(Request $request)`: Processes CSV files in the background queue, registering user attributes and dispatching invitation emails.

### `AdminController`
- **Routes**: `/admin/configuration`, `/admin/offices`
- **Methods**:
  - `configuration()`: Settings interface displaying active system snapshots.
  - `triggerManualBackup()`: Compresses database logs and storage directories into `.tar.gz` and stores them in `/storage/app/backups/`.
  - `restoreSnapshot(SystemBackup $backup)`: Restores database tables and assets using a backup file.

### `SecurityController`
- **Routes**: `/admin/security`
- **Methods**:
  - `index()`: The Isolation Forest dashboard console displaying anomalous activity logs.
  - `markSafe(User $user, AuditLog $log)`: Clears warnings and marks a log entry as normal.
  - `markSuspended(User $user, AuditLog $log)`: Temporarily suspends the account status to block access.

---

## 3. Coordinator Controllers (`App\Http\Controllers\Coordinator`)

These controllers enable academic coordinators to monitor student progress.

### `DashboardController`
- **Routes**: `/coordinator/dashboard`
- **Methods**:
  - `index()`: Renders total intern count, active deployments, and student compliance charts.

### `InternController`
- **Routes**: `/coordinator/interns`
- **Methods**:
  - `index()`: Renders the student roster filterable by deployment status.
  - `show(User $user)`: Detail view containing the student's task output rate, daily attendance logs, and evaluation reports.

---

## 4. Head Supervisor Controllers (`App\Http\Controllers\HeadSupervisor`)

These controllers implement deployment evaluations and matching.

### `OfficeMatchingController`
- **Namespace**: `App\Http\Controllers\HeadSupervisor`
- **Routes**: `/head-supervisor/matching`
- **Methods**:
  - `index()`: Renders Rete matching recommendations and match percentages.
  - `storeEvaluation(StoreEvaluationRequest $request, OnboardingApplicant $applicant)`: Saves interview scoring criteria and tech assessments, then immediately invokes the Rete matching service.
  - `finalizeMatches(Request $request)`: Finalizes placements, creates official user credentials, and alerts interns of deployment.

### `AnalyticsController`
- **Routes**: `/head-supervisor/analytics`
- **Methods**:
  - `index()`: Aggregates hour logs, completion ratios, and late trends across all SDO QC offices.

---

## 5. Supervisor Controllers (`App\Http\Controllers\Supervisor`)

These controllers manage office deliverables and daily task tracking.

### `TaskController`
- **Routes**: `/supervisor/tasks`
- **Methods**:
  - `store(Request $request)`: Creates and assigns tasks to individual interns or specific groups.
  - `updateStatus(Request $request, Task $task)`: Sets submission statuses (`Completed`, `Pending Revision`, `Overdue`).
  - `destroy(Task $task)`: Deletes an assigned task.

### `OfficeManagementController`
- **Routes**: `/supervisor/office`
- **Methods**:
  - `updateRequirements(Request $request)`: Sets required technical skills and majors.
  - `updateRules(Request $request)`: Sets rule bounds for placement matching.

---

## 6. Intern Controllers (`App\Http\Controllers\Intern`)

These controllers manage daily student activities.

### `AttendanceController`
- **Namespace**: `App\Http\Controllers\Intern`
- **Routes**: `/intern/attendance`
- **Methods**:
  - `clockIn(Request $request)`: Resolves browser GPS coordinates, calculates distance delta using the Haversine formula, and registers clock-in times.
  - `clockOut(Request $request)`: Validates location coords and registers clock-out times.

### `ProgressController`
- **Routes**: `/intern/progress`
- **Methods**:
  - `index()`: Renders active assignments and completion status.
  - `submitTask(Request $request, Task $task)`: Submits accomplishment reports, text journals, and screenshots.

---

## 7. Global Controllers (`App\Http\Controllers`)

### `MFAController`
- **Routes**: `/mfa/setup`, `/mfa/verify`
- **Methods**:
  - `setup()`: Generates TOTP secrets and renders setup QR codes.
  - `verify(Request $request)`: Asserts valid 6-digit TOTP codes and prevents replay actions using code burning.

### `SubmissionController`
- **Methods**:
  - `store(Request $request)`: Saves screenshot images and documents into private storage paths.
