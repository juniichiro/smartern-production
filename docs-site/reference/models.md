# Models & Schema Reference

This reference catalog details all Eloquent models in the `app/Models/` namespace.

---

## 1. User & Account Model Structure

These models map account credentials and profile attributes.

### `User.php`
Represents system users. Matches the `users` table.
- **Attributes**:
  - `email` (string, unique): Login identifier.
  - `password` (string): Hashed credential.
  - `role_id` (integer): Links to the `roles` table.
  - `google2fa_secret` (string, encrypted): Seed for TOTP MFA.
  - `google2fa_enabled` (boolean): Flags MFA status.
  - `status` (string): Options include `active`, `suspended`, `onboarding`.
- **Relationships**:
  - `role()`: BelongsTo `Role`.
  - `internDetails()`: HasOne `InternDetail`.
  - `supervisorDetails()`: HasOne `SupervisorDetail`.
  - `coordinatorDetails()`: HasOne `SchoolCoordinatorDetail`.
  - `auditLogs()`: HasMany `AuditLog`.

### `InternDetail.php`
Details student interns. Matches `intern_details`.
- **Attributes**:
  - `user_id` (integer): Foreign key to `users`.
  - `office_id` (integer, nullable): Deployed office.
  - `school` (string): School identifier.
  - `phone_number` (string): Format: `+63XXXXXXXXXX`.
  - `avatar_path` (string): Avatar asset location.
  - `emergency_contact_name` (string) & `emergency_contact_phone` (string)
- **Relationships**:
  - `user()`: BelongsTo `User`.
  - `office()`: BelongsTo `Office`.

### `SupervisorDetail.php`
Details office supervisors. Matches `supervisor_details`.
- **Attributes**:
  - `user_id` (integer): Foreign key to `users`.
  - `office_id` (integer): Deployed office.
  - `is_hr_head` (boolean): Flag for head privileges.
- **Relationships**:
  - `office()`: BelongsTo `Office`.

### `SchoolCoordinatorDetail.php`
Details academic coordinators. Matches `school_coordinator_details`.
- **Attributes**:
  - `school` (string): Overseen school.
- **Relationships**:
  - `user()`: BelongsTo `User`.

### `Role.php`
Defines role identifiers. Matches `roles`.
- **Attributes**:
  - `name` (string): e.g., `admin`, `super_admin`, `head_supervisor`, `supervisor`, `school_coordinator`, `intern`.

---

## 2. Workplace & Placement Model Structure

These models govern physical offices, Rete matching rules, and placement results.

### `Office.php`
Represents physical SDO QC offices. Matches `offices`.
- **Attributes**:
  - `name` (string): Office title.
  - `latitude` & `longitude` (double): Geofence coordinate bounds.
  - `radius` (integer): Allowed boundary in meters.
- **Relationships**:
  - `interns()`: HasMany `InternDetail`.
  - `rules()`: HasMany `OfficeRule`.

### `OfficeRule.php`
Defines criteria processed by the Rete algorithm. Matches `office_rules`.
- **Attributes**:
  - `office_id` (integer): Foreign key to `offices`.
  - `rule_name` (string): Rule description.
  - `criteria` (json): Array of skills, GPA, and major guidelines.

### `MatchResult.php`
Stores results from the matching daemon. Matches `match_results`.
- **Attributes**:
  - `applicant_name` (string): Candidate name.
  - `office_id` (integer): Foreign key to `offices`.
  - `score` (integer): Match rating (0-100).
  - `confidence` (string): `High`, `Medium`, or `Low`.
  - `is_confirmed` (boolean): Set to `true` when matching finalizes.

### `OnboardingApplicant.php`
Candidate database table before matching. Matches `onboarding_applicants`.
- **Attributes**:
  - `full_name` (string), `email` (string), `university` (string).
  - `tech_skills` & `soft_skills` (json): Declared abilities.
  - `resume_path` (string): PDF resume location.

### `ApplicantEvaluation.php`
Details interview assessments. Matches `applicant_evaluations`.
- **Attributes**:
  - `applicant_id` (integer): Foreign key to `onboarding_applicants`.
  - `verified_skills` (json): Verified abilities.
  - `behavioral_score` (integer): Scale of 1 to 5.
  - `is_recommended` (boolean): Flag for head supervisor reviews.

---

## 3. Operations & Workflow Model Structure

These models represent logs, tasks, files, and updates.

### `Attendance.php`
Tracks daily coordinates. Matches `attendance`.
- **Attributes**:
  - `user_id` (integer), `office_id` (integer).
  - `clock_in_at` & `clock_out_at` (timestamps).
  - `clock_in_lat` / `clock_in_lon` & `clock_out_lat` / `clock_out_lon` (double).
  - `is_geofence_bypassed` (boolean).

### `Task.php`
Represents assignments. Matches `tasks`.
- **Attributes**:
  - `title` (string), `description` (text), `deadline` (datetime).
- **Relationships**:
  - `submissions()`: HasMany `Submission`.

### `Submission.php`
Tracks task submissions. Matches `submissions`.
- **Attributes**:
  - `task_id` (integer), `user_id` (integer).
  - `journal_text` (text): Accomplishment details.
  - `file_path` (string): Screenshot asset location.

### `AbsenceRequest.php`
Processes leave requests. Matches `absence_requests`.
- **Attributes**:
  - `user_id` (integer), `reason` (text), `status` (`pending`, `approved`, `rejected`).

### `AuditLog.php`
Stores system logs analyzed by the ML daemon. Matches `audit_logs`.
- **Attributes**:
  - `user_id` (integer, nullable).
  - `action` (string), `ip_address` (string), `user_agent` (text), `payload` (json).
  - `processed` (boolean): Flags ML daemon scanning completion.
  - `anomaly_score` (float): Value between 0.0 and 1.0.

### `Certificate.php` & `CertificateTemplate.php`
- Handles certificate configurations and generated assets.

### `SystemBackup.php`
- Tracks backup database snapshots.
