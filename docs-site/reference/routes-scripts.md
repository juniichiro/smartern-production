# Routes & Python Scripts Catalog Reference

This document provides a technical directory of Laravel web routing groups and the standalone Python scripts.

---

## 1. Routes Catalog (`routes/web.php`)

All application routes are defined inside `routes/web.php`. They are structured into role-restricted middleware groups.

### General Public Routes
- **`/`** (GET): Main public landing page.
- **`/login`** (GET/POST): Login page and submission.
- **`/logout`** (POST): Terminates session.
- **`/register`** (GET/POST): Onboarding coordinator registration.

### Admin Protected Group
- **Middleware**: `auth`, `verified`, `role:admin`
- **Prefix**: `/admin`
- **Route Names**: `admin.*`
- **Catalog**:
  - `/admin/dashboard` (GET) → `AdminDashboardController`
  - `/admin/users` (GET/POST) → `AccountController@index` / `AccountController@store`
  - `/admin/users/{user}` (PUT/DELETE) → `AccountController@update` / `AccountController@destroy`
  - `/admin/users/import` (POST) → `AccountController@importBulkCSV`
  - `/admin/security` (GET) → `SecurityController`
  - `/admin/security/mark-safe/{user}/{log}` (POST) → `SecurityController@markSafe`
  - `/admin/security/mark-suspended/{user}/{log}` (POST) → `SecurityController@markSuspended`
  - `/admin/configuration` (GET) → `AdminController@configuration`
  - `/admin/configuration/backup` (POST) → `AdminController@triggerManualBackup`
  - `/admin/offices` (GET) → `AdminController@offices`
  - `/admin/docs/{path?}` (GET) → `DocsController@index` (Secured file server)

### Head Supervisor Protected Group
- **Middleware**: `auth`, `verified`, `role:head_supervisor`
- **Prefix**: `/head-supervisor`
- **Route Names**: `head-supervisor.*`
- **Catalog**:
  - `/head-supervisor/dashboard` (GET) → `DashboardController`
  - `/head-supervisor/matching` (GET) → `OfficeMatchingController`
  - `/head-supervisor/matching/evaluate/{applicant}` (POST) → `OfficeMatchingController@storeEvaluation`
  - `/head-supervisor/matching/finalize` (POST) → `OfficeMatchingController@finalizeMatches`
  - `/head-supervisor/analytics` (GET) → `AnalyticsController`

### Supervisor Protected Group
- **Middleware**: `auth`, `verified`, `role:supervisor`
- **Prefix**: `/supervisor`
- **Route Names**: `supervisor.*`
- **Catalog**:
  - `/supervisor/dashboard` (GET) → `DashboardController`
  - `/supervisor/tasks` (GET/POST) → `TaskController@index` / `TaskController@store`
  - `/supervisor/tasks/{task}/status` (PATCH) → `TaskController@updateStatus`
  - `/supervisor/announcements` (GET/POST) → `AnnouncementController`

### Intern Protected Group
- **Middleware**: `auth`, `verified`, `role:intern`
- **Prefix**: `/intern`
- **Route Names**: `intern.*`
- **Catalog**:
  - `/intern/dashboard` (GET) → `DashboardController`
  - `/intern/attendance` (GET) → `AttendanceController`
  - `/intern/attendance/clock-in` (POST) → `AttendanceController@clockIn`
  - `/intern/attendance/clock-out` (POST) → `AttendanceController@clockOut`
  - `/intern/progress` (GET/POST) → `ProgressController`

---

## 2. Standalone Python Scripts (`scripts/`)

Stand-alone modules running calculations or background processes.

### `scripts/if_daemon.py`
- **Role**: Continuous background daemon calculating Isolation Forest anomaly scores.
- **Dependencies**: `pandas`, `scikit-learn`, `numpy`, `mysql-connector-python` (or `sqlite3` depending on configuration).
- **Execution Flow**:
  1. Opens a database socket.
  2. Queries unprocessed audit logs:
     ```sql
     SELECT * FROM audit_logs WHERE processed = 0 ORDER BY created_at ASC;
     ```
  3. Formulates a feature matrix:
     - `IP_divergence`
     - `consecutive_time_delta`
     - `action_count`
  4. Predicts scores using the pre-fit estimator model.
  5. Updates logs with the output score:
     ```sql
     UPDATE audit_logs SET anomaly_score = %s, processed = 1 WHERE id = %s;
     ```
  6. Dispatches standard signal notifications if score is $\ge 0.7$.

### `scripts/rete_match.py`
- **Role**: Computes placement matching scores based on a Rete network structure.
- **Dependencies**: `numpy`, `pandas`.
- **Execution Flow**:
  1. Reads JSON data from `stdin` containing candidates and rules.
  2. Parses candidate tech skills, soft skills, major, and interview recommendations.
  3. Builds an Alpha network filter mapping properties.
  4. Joins variables in the Beta network.
  5. Generates match scores (0-100) and confidence tiers (`High`, `Medium`, `Low`).
  6. Outputs a JSON string to `stdout` for the PHP parent process to parse.
