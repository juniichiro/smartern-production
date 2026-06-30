# Middlewares & Services Catalog Reference

This document provides a technical reference for custom middlewares and domain service classes.

---

## 1. Application Middlewares (`App\Http\Middleware`)

Middlewares intercept incoming HTTP requests to validate security parameters before routing them to controllers.

### `RoleMiddleware`
- **Path**: `app/Http/Middleware/RoleMiddleware.php`
- **Method**: `handle(Request $request, Closure $next, ...$roles)`
- **Behavior**:
  - Checks if a user session is active.
  - Checks the user's role name (`$request->user()->role->name`).
  - **Superadmin Bypass**: If the role is exactly `super_admin` (role ID `6`), access is immediately allowed.
  - Matches the role name against the allowed `$roles` list.
  - Aborts with a `403 Unauthorized` page if there is no match.

### `MfaMiddleware` (or custom check filters)
- **Path**: Custom application filter hooks.
- **Behavior**:
  - Inspects if the authenticated user has MFA enabled (`google2fa_enabled`).
  - If enabled but not verified in the current session, it redirects the request to `/mfa/verify`.
  - If a new user is marked as `onboarding` status, it forces redirection to `/mfa/setup`.

### `GeofenceCheck` (Geospatial verification filter)
- **Behavior**:
  - Intercepts requests to clock-in/out.
  - Extracts the client's latitude and longitude parameters.
  - Checks the assigned office latitude, longitude, and radius constraints.
  - Invokes `AttendanceService` to check the great-circle distance.
  - Aborts with `422 Unprocessable Entity` if the client is out of bounds.

---

## 2. Core Application Services (`App\Services`)

Services encapsulate complex domain logic, database operations, and process execution.

### `SmartMatchingService.php`
- **Namespace**: `App\Services`
- **Purpose**: Runs placement matching for onboarding candidates using Rete scripts.
- **Key Methods**:
  - `runMatchingForApplicant(OnboardingApplicant $applicant)`:
    - **Parameters**: `OnboardingApplicant $applicant` (loaded with evaluations).
    - **Returns**: `boolean` (success/failure status).
    - **Logic**: Parses the resume file, reads candidate tech/soft skills, compiles rule bounds, and executes the Python child process (`rete_match.py`).
  - `processMatchingBatch(array $allStudentData, $rules)`:
    - **Parameters**: `array $allStudentData` (facts), `Collection $rules` (office constraints).
    - **Returns**: `boolean`.
    - **Logic**: Deletes older unconfirmed match entries for the student and creates new `MatchResult` records.
  - `extractResumeText($path)`:
    - **Parameters**: `string $path` (relative file path to the resume PDF).
    - **Returns**: `string` (extracted plain text).

### `AnomalyDetectionService.php`
- **Purpose**: Integrates the Laravel application with the Isolation Forest daemon.
- **Key Methods**:
  - `scanRecentActivityLogs()`:
    - **Returns**: `array` (list of flagged anomalous logs).
    - **Logic**: Invokes `scripts/if_daemon.py` on pending logs and returns computed anomaly scores.
  - `flagLogEvent(AuditLog $log, float $score)`:
    - **Parameters**: `AuditLog $log`, `float $score` (anomaly score 0.0 - 1.0).
    - **Behavior**: Writes the score to the log. If the score is $\ge 0.7$, it triggers an email notification to admins and broadcasts the event over WebSockets via Reverb.

### `AttendanceService.php`
- **Purpose**: Operates the geospatial logic for geofenced clocking.
- **Key Methods**:
  - `clockIn(User $user, float $latitude, float $longitude)`:
    - **Parameters**: `User $user`, `float $latitude`, `float $longitude`.
    - **Returns**: `Attendance` (created log model).
    - **Logic**: Verifies distance constraints. If valid, writes the clock-in timestamp and coordinates.
  - `calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2)`:
    - **Parameters**: `float $lat1`, `float $lon1` (point A), `float $lat2`, `float $lon2` (point B).
    - **Returns**: `float` (distance in meters).
    - **Logic**: Implements the Haversine equation to find the great-circle distance between coordinates.

### `SecurityAuditService.php`
- **Purpose**: Creates audit footprint logs.
- **Key Methods**:
  - `log(string $action, string $description, array $payload = [])`:
    - **Parameters**: `string $action` (e.g. `login_attempt`), `string $description`, `array $payload`.
    - **Behavior**: Registers the current client IP address, user agent, and payload details in the `audit_logs` table.
