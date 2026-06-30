# Authorization & Privacy Policies Reference

This document outlines the model-level authorization rules (Laravel Policies) and the user data privacy standards implemented in SmarTern.

---

## 1. Model Authorization Policies (`App\Policies`)

Laravel Policies intercept action requests on specific models, ensuring users cannot query or mutate records belonging to other users or departments.

### `AbsenceRequestPolicy`
- **Class**: `app/Policies/AbsenceRequestPolicy.php`
- **Rules**:
  - `view(User $user, AbsenceRequest $request)`: Permits interns to view only their own requests. Admins, coordinators, and the assigned supervisor can view the request.
  - `create(User $user)`: Restricted to active interns.
  - `update(User $user, AbsenceRequest $request)`: Authorizes supervisors and coordinators to approve/reject leave requests.

### `AnnouncementPolicy`
- **Class**: `app/Policies/AnnouncementPolicy.php`
- **Rules**:
  - `create(User $user)` / `update(User $user)`: Restricted to supervisors, head supervisors, and administrators.
  - `view(User $user, Announcement $announcement)`: Restricts announcements to users associated with the target office department.

### `AttendancePolicy`
- **Rules**:
  - `view(User $user, Attendance $attendance)`: Permits interns to view their own attendance logs. Supervisors can view logs for interns in their office.

### `CertificatePolicy`
- **Rules**:
  - `download(User $user, Certificate $certificate)`: Permits the specific intern to download their certificate. Issue actions are restricted to head supervisors and admins.

### `SubmissionPolicy`
- **Rules**:
  - `create(User $user, Task $task)`: Assures only the intern assigned to the task can upload accomplishments or screenshots.

### `TaskPolicy`
- **Rules**:
  - `create(User $user)`: Restricted to office supervisors.
  - `update(User $user, Task $task)`: Validates that only the supervisor who created the task (or administrators) can edit deadlines or modify criteria.

---

## 2. System Privacy & Data Policy (SDO QC Compliance)

SmarTern processes personal, geospatial, and system auditing data. The following compliance rules protect user data.

### Geofencing GPS Coordinates
- **Data Capture**: Latitude, longitude, and device mock indicators are captured only at the exact moments of clock-in and clock-out operations.
- **Location Privacy**: Coordinates are stored in the secure database server and are only evaluated against the office geofence radius. Continuous background location tracking is **never** executed.

### Emergency Contact Information
- **Data Capture**: Intern details profile emergency names and mobile numbers.
- **Access Control**: These fields are visible only to the school coordinator, the supervisor, and system administrators. They are restricted from public search directories.

### Audit Logs & Footprints Retention
- **Data Capture**: Login timestamps, IP addresses, and user-agent string headers are recorded in the `audit_logs` table for ML anomaly scans.
- **Pruning Cycle**: To protect user footprint histories, logs are subject to a **90-day retention loop**. The daily scheduled task `app:archive-audit-logs --days=90` extracts logs older than 90 days, compresses them into encrypted Gzip archives, and prunes them from the active database.
