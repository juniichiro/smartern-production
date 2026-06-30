# Controllers & Models

This section provides a detailed breakdown of the controllers and models used in the SmarTern codebase to manage the internship lifecycle.

---

## 1. Controllers Breakdown

SmarTern segments controller operations into role-specific namespaces located under `app/Http/Controllers/`.

### Admin Controllers (`app/Http/Controllers/Admin/`)
- **`AccountController.php`**: Manages CRUD operations for all user types (interns, supervisors, coordinators). Handles role-based visibility restrictions, automated password creation, and bulk CSV imports.
- **`AdminController.php`**: Controls system configurations, registers departments/offices, manages manual backup snapshots, and handles geofencing boundary values.
- **`SecurityController.php`**: Operates the interface for the Isolation Forest output. Displays anomalies, generates reports, and allows admins to temporarily restrict flagged accounts.

### Supervisor Controllers (`app/Http/Controllers/Supervisor/`)
- **`TaskController.php`**: Enables supervisors to assign deliverables to interns, verify completed submissions, and manage revision feedback.
- **`AnnouncementController.php`**: Allows broadcasting department notices to interns via pushing and email notifications.
- **`DashboardController.php`**: Computes and displays department analytics (counts, completions, attendance trends).
- **`AttendanceController.php`**: Inspects local logs, manages student schedules, and reviews daily logs.
- **`OfficeManagementController.php`**: Configures requirements and rules for automated matching constraints.

### Head Supervisor Controllers (`app/Http/Controllers/HeadSupervisor/`)
- **`OfficeMatchingController.php`**: Controls the Rete Matching module interface, allowing Ms. Mal-in to verify assessments, run placement evaluations, and finalize assignments.
- **`AnalyticsController.php`**: Generates consolidated multi-department internship metrics.

### Intern Controllers (`app/Http/Controllers/Intern/`)
- **`AttendanceController.php`**: Manages GPS geofence clock-in and clock-out actions.
- **`JournalController.php`** (or accomplishment logs): Log entry submissions describing daily work results.

---

## 2. Models Breakdown

Models are located under `app/Models/` and extend Eloquent to represent system entities.

### Core User Directory
- **`User.php`**: Main credential holder. Manages TOTP authentication parameters (`google2fa_secret`), login attempts, account status, and role relationships.
- **`InternDetail.php`**: Profiles student interns. Stores formal ID pictures, organization emails, contact numbers, deployed office ID, and Emergency Contact details.
- **`SupervisorDetail.php`**: Profiles supervisors. Identifies office association and flags status flags like `is_hr_head`.
- **`SchoolCoordinatorDetail.php`**: Links academic coordinators to the list of interns they oversee.

### Attendance & Workflow
- **`Attendance.php`**: Records clock-in/out timestamps, latitude/longitude coords, and validates if geofencing constraints were bypassed.
- **`Task.php`**: Holds title, description, deadlines, and supervisor ID.
- **`Submission.php`**: Connects intern work uploads and screenshots to assigned tasks.
- **`AbsenceRequest.php`**: Processes leave files and validation flags.

### Security & Automated Logic
- **`AuditLog.php`**: Records chronological system footprints (logins, uploads, deletions) used by the Isolation Forest daemon to calculate anomaly scores.
- **`OnboardingApplicant.php`**: Stores incoming student profiles before final deployment.
- **`ApplicantEvaluation.php`**: Holds supervisor interview ratings and verified skill assessments.
- **`OfficeRule.php`**: Stores requirements (languages, skills, major preferences) compiled into Rete facts.
- **`MatchResult.php`**: Holds placement scores and confidence evaluations returned by the Rete placement script.
