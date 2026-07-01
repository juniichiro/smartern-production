# Security Implementations & Practices

This section outlines the basic and advanced security layers implemented in SmarTern to defend the application against common vulnerabilities.

---

## 1. Web Vulnerability Protections

SmarTern incorporates standard OWASP safeguards to protect application routes and database connections.

### Cross-Site Request Forgery (CSRF)
- **Mechanism**: Every state-changing request (POST, PUT, PATCH, DELETE) is intercepted by Laravel's standard CSRF middleware.
- **Implementation**: Forms require the `@csrf` Blade directive to generate a session-bound validation token. AJAX requests from Axios automatically compile this token from the `XSRF-TOKEN` cookie.

### SQL Injection Prevention
- **Mechanism**: Use of raw database queries is restricted.
- **Implementation**: SmarTern interacts with the database exclusively through Laravel's **Eloquent ORM** and the Query Builder. Under the hood, these tools use PDO parameter binding (prepared statements) for all query variables, ensuring that user inputs are never executed as database commands.

### Cross-Site Scripting (XSS) Sanitization
- **Mechanism**: Protects fields that allow user markup inputs (such as accomplishment journals and supervisor announcements).
- **Implementation**:
  - The application uses `ezyang/htmlpurifier` to clean incoming text variables, stripping javascript tags, inline execution scripts, and CSS injections.
  - Blade templates output variables using safe escaping tags <code v-pre>{{ $variable }}</code> which automatically execute HTML entity escaping, restricting unsafe tags from rendering.

### Directory / Path Traversal Guard
- **Mechanism**: Blocks file access hacks.
- **Implementation**: When the dynamic files server (`DocsController.php`) maps wildcard requests into storage directory lookups, it validates the request path:
  ```php
  if (str_contains($path, '..') || str_contains($path, '\\')) {
      abort(404);
  }
  ```
  This check guarantees users cannot access system configs, logs, or other system files outside the target documentation folder.

---

## 2. Access Controls & Cryptography

### Route Authorization
- **Mechanism**: Multi-role route partitioning.
- **Implementation**: Routes are partitioned using the custom `RoleMiddleware`. The middleware inspects the authenticated user's role and aborts with a 403 error page if the user does not possess the correct privileges. Administrators and super_admins bypass these standard validation checks.

### Password Cryptography & Security
- **Mechanism**: Secure credential storage.
- **Implementation**:
  - Passwords are encrypted using the **bcrypt** hashing algorithm.
  - Standard registration and update validation layers enforce strong password rules: a minimum of 12 characters containing a combination of uppercase letters, lowercase letters, numbers, and special symbols.

### Identifier Obfuscation (`Hashids`)
- **Mechanism**: Prevents direct database ID exposure (preventing ID enumeration / harvesting).
- **Implementation**: Database IDs (e.g. `/intern/tasks/12`) are obfuscated before rendering using the `vinkla/hashids` package, transforming sequential database primary keys into short, non-sequential hashes (e.g., `/intern/tasks/vb2VMy13`).

---

## 3. Advanced Security Modules

### Multi-Factor Authentication (MFA)
- **Single-Use Replay Guard**: To protect against authentication replay attacks within the 30-second window, validated TOTP code timestamps are logged in the database cache. Submitting the same code twice within its active 30-second index results in an immediate rejection.

### Geofencing Coordinates Enforcement
- **Location Spoofing Prevention**: Coordinates validation is performed strictly on the server side using the Haversine equation rather than relying on browser-side triggers. Bypasses and abnormal distance variables are immediately flagged and logged to the `audit_logs` table for ML scan processing.

### Single Session & Device Profiling (`CheckSingleSession` Middleware)
- **Session Hijacking Guard**: Enforces that only one active session can exist per user account across all browsers. When a user logs in, their current session ID is cached (`user-session-{id}`). If the cached session ID differs from the request's session ID, the user is logged out automatically.
- **Client & Device ID Verification**: Validates browser fingerprints (User-Agent, IP address) and binds them with unique cookies (`smartern_device_id` and `smartern_client_id`). If any parameter changes mid-session, the session is invalidated.
- **Dynamic Client ID Synchronization**: To prevent race conditions when logging in from pages that have not yet initialized browser device binding (e.g. guest pages), the middleware dynamically synchronizes the browser client ID with the session store if it was empty.

### Inactivity Session Timeout Manager
- **Heartbeat Verification**: Authenticated pages query `/session/ping` every 10 seconds. If the server session is expired (returns `401`), the user is immediately redirected to `/login?timeout=1`.
- **Client-Side Inactivity Tracking**: User interaction events (clicks, keypresses, scrolls) keep resetting the activity timestamp in `localStorage`. If no activity is detected for 30 minutes, a warnings modal is displayed for 10 seconds before auto-logging out the session.
- **Stale Tab Lock Prevention**: To prevent stale background tabs from logging out active sessions (e.g. after a user logs in on a new tab), the timeout logout handler verifies the latest activity timestamp in `localStorage` right before sending the logout request.
- **Authentication Gateway Reset**: All guest entry forms (login, reset password, change password) clear the activity store upon submission to ensure that guest AFK time never interferes with active session timers.
