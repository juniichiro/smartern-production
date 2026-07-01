# Artisan Console Commands Reference

This reference details the custom Artisan CLI commands defined in `app/Console/Commands/` and `routes/console.php`.

---

## 1. System Maintenance & Security Commands

### `app:recalculate-anomaly-scores`
- **Signature**: `php artisan app:recalculate-anomaly-scores`
- **Schedule**: Runs every minute.
- **Description**: Recalculates ML anomaly scores for unprocessed audit logs.
- **How it works**:
  1. Obtains a cache lock (`recalculate_anomaly_scores_lock`) to prevent concurrent executions.
  2. Resolves `AnomalyDetectionService` and calls `recalculateScores()`.
  3. Queries unprocessed log rows and invokes `scripts/if_daemon.py` to evaluate foot prints.
  4. Releases the cache lock.

### `app:archive-audit-logs`
- **Signature**: `php artisan app:archive-audit-logs {--days=90}`
- **Options**:
  - `--days`: The number of days of hot logs to retain in the database (default: `90`).
- **Schedule**: Runs daily.
- **Description**: Archives older audit logs into a compressed Gzip file and prunes them from the database.
- **How it works**:
  1. Computes a cutoff date based on the specified `--days` option.
  2. Creates the target archive directory (`storage/app/audit_archives/`) if it does not exist.
  3. Writes older logs into a temporary JSON file and compresses it into a `.gz` archive.
  4. Deletes the archived rows from the database.

---

## 2. Backup & Restore Commands

### `backup:run`
- **Signature**: `php artisan backup:run {--type=manual} {--admin_id=}`
- **Options**:
  - `--type`: Typology of the backup (`manual` or `auto`, default: `manual`).
  - `--admin_id`: User ID of the administrator triggering the backup.
- **Schedule**: Automatically runs daily at **7:00 PM** (`dailyAt('19:00')`) with `--type=auto`.
- **Description**: Executes a full database and storage backup.
- **How it works**:
  1. Performs a database dump.
  2. Compresses the database dump and files inside `storage/app/public/` into a single ZIP archive.
  3. Saves the archive inside `storage/app/backups/`.
  4. Creates a `SystemBackup` record documenting the file size, path, and creator.

### `backup:restore`
- **Signature**: `php artisan backup:restore {id}`
- **Arguments**:
  - `id` (Required): The ID of the target `SystemBackup` record.
- **Description**: Restores the system database and storage folders to a specific backup snapshot.
- **How it works**:
  1. Locates the backup record.
  2. Places the system in a temporary maintenance mode.
  3. Unzips the backup archive, restores database tables, and replaces storage assets.
  4. Clears system caches and exits maintenance mode.

---

## 3. Placement & Simulation Commands

### `matching:run`
- **Signature**: `php artisan matching:run {id?}`
- **Arguments**:
  - `id` (Optional): ID of a specific `OnboardingApplicant` to evaluate.
- **Description**: Runs Rete office placement calculations for candidates.
- **How it works**:
  - If an ID is passed, matching runs specifically for that applicant.
  - If no ID is passed, it processes all onboarding applicants sequentially.
  - Resolves `SmartMatchingService` to run calculations via the Python engine.

### `app:seed-ml-baseline`
- **Signature**: `php artisan app:seed-ml-baseline {--ip=130.105.10.15}`
- **Options**:
  - `--ip`: The IP address to bind to generated normal events (default: `130.105.10.15`).
- **Description**: Seeds the audit logs with a historical baseline of healthy activity to train the ML Isolation Forest model.

### `app:simulate-attendance`
- **Signature**: `php artisan app:simulate-attendance`
- **Description**: Simulates a full week of attendance plus absence requests for the test intern `test_intern@smartern.com`.

---

## 4. Scheduling & Attendance Commands

### `app:mark-absent`
- **Signature**: `php artisan app:mark-absent`
- **Schedule**: Runs every 15 minutes.
- **Description**: Automatically logs absences and performs auto-checkouts for interns at the end of their shifts.
- **How it works**:
  1. Queries all active interns.
  2. Retrieves their current effective schedules.
  3. Checks if the intern clocked in today. If they did not and the current time is past their schedule's checkout time (including a 15-minute grace period), creates an `Attendance` record marked as `absent`.
  4. If the intern clocked in but forgot to clock out, and the checkout time has passed, invokes `AttendanceService` to perform an automated clock-out using their original clock-in coordinates.
