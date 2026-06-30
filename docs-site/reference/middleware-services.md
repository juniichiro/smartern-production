# Middleware & Service Layer Implementation

This section details the custom middlewares, transaction services, and dependencies managing SmarTern's business logic.

---

## 1. Middleware Architectures

Laravel Middlewares filter HTTP requests before they reach the controller methods. They are registered in `app/Http/Kernel.php` (or inside `bootstrap/app.php` in Laravel 11/13 setup).

### `RoleMiddleware` (`app/Http/Middleware/RoleMiddleware.php`)
Ensures role integrity across routing groups.
- **Method signature**:
  ```php
  public function handle(Request $request, Closure $next, ...$roles)
  ```
- **Rules**:
  1. Checks if a user is authenticated (`$request->user()`).
  2. Extracts the role name string from `$request->user()->role->name`.
  3. Bypasses checks if the role is exactly `super_admin`.
  4. Loops through the parameters `$roles` and executes matching:
     ```php
     if ($userRole === strtolower($role)) { return $next($request); }
     ```
  5. Aborts with a `403` HTTP status if no match occurs.

### `GeofenceCheck`
Verifies location checks for attendance routes.
- **Rules**:
  1. Extracts GPS latitude, longitude, and device mock status headers from the request payload.
  2. Fetches the assigned office geofence data via the user relationship (`$request->user()->internDetails->office`).
  3. Invokes the `AttendanceService@calculateDistance` logic.
  4. If the distance exceeds the office radius, returns a `422 Unprocessable Entity` JSON response and writes a warning entry to the `audit_logs` table.

---

## 2. Service Layer Class Implementations

Services isolate complex procedures from controllers, promoting class reuse.

### `SmartMatchingService` (`app/Services/SmartMatchingService.php`)
- **Key Method**: `runMatchingForApplicant(OnboardingApplicant $applicant)`
  - Invokes text parser helper logic to read resumes.
  - Groups rules from the `OfficeRule` model.
  - Compiles facts and executes `rete_match.py` via Python processes.
- **Key Method**: `processMatchingBatch(array $allStudentData, $rules)`
  - Parses JSON outputs, clears old unconfirmed rows, and registers new matches into the `MatchResult` table.

### `AnomalyDetectionService` (`app/Services/AnomalyDetectionService.php`)
- **Key Method**: `scanRecentActivityLogs()`
  - Triggers log checks.
  - Communicates with `if_daemon.py` to evaluate recent system logs.
  - Triggers email broadcasts if high-risk anomalies ($\ge 0.7$) are detected.

---

## 3. Dynamic File Server (`DocsController.php`)

To protect developer documentation from public search engines and unauthenticated users, `DocsController` serves compiled VitePress files dynamically.

```
Request (/admin/docs/assets/app.js)
                 │
                 ▼
          RoleMiddleware
     Is Admin or SuperAdmin?
         ├── YES ──► DocsController@index('assets/app.js')
         │                 │
         │                 ├── File Exists?
         │                 │     ├── YES ──► Map Mime Type (e.g. 'js' -> 'application/javascript')
         │                 │     │           Return response()->file($path, $headers)
         │                 │     └── NO  ──► Abort 404
         │                 │
         └── NO ───► Abort 403 (Unauthorized)
```

- **MIME mapping matrix**:
  - CSS: `text/css`
  - JS: `application/javascript`
  - HTML: `text/html`
  - PNG/JPG: `image/png` / `image/jpeg`
  - SVG: `image/svg+xml`
- **Security Check**: Enforces traversal safety filters by blocking `..` and `\` strings inside the `$path` wildcard argument to prevent files outside `docs-site/dist/` from being exposed.
