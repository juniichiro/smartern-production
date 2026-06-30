# Middleware, Services & Scripts

This section details the custom middlewares, application services, and background daemon scripts that implement security checks and placement algorithms.

---

## 1. Middleware Layer

Located in `app/Http/Middleware/` and registered in your route pipeline:

- **`RoleMiddleware.php`**: Evaluates `Auth::user()->role->name` against permitted routes. It features an automated bypass for the `super_admin` role, letting superadmins bypass manual checks across all modules.
- **`MfaMiddleware`** (or verification filters): Validates that users with configured MFA cannot bypass verification, and redirects onboarding accounts without configured MFA to the configuration setup.
- **`GeofenceCheck`**: Runs in the attendance logging route to ensure coordinates match the target office radius before letting form processing complete.

---

## 2. Application Services

Located in `app/Services/` to encapsulate core domain logic:

### `SmartMatchingService.php`
- Orchestrates the Rete placement engine.
- Extracts resume texts from PDF paths.
- Compiles intern profiles and `OfficeRule` constraints into JSON and executes `scripts/rete_match.py` via a Symfony Process.
- Parses output matches and populates the `MatchResult` table.

### `AnomalyDetectionService.php`
- Interacts with the Python Isolation Forest daemon.
- Reads audit logs, validates threshold triggers, and formats anomaly parameters.
- Dispatches emergency notifications and emails to administrators when a high-risk score is returned.

### `AttendanceService.php`
- Handles the core geospatial calculations.
- Evaluates distance equations (using the Haversine formula) to verify if an intern's coordinates fall within their office's allowed geofence radius.

---

## 3. Background Python Scripts

Located in `scripts/` and run asynchronously:

### `if_daemon.py` (Isolation Forest Daemon)
- **Role**: Active ML daemon scanning user footprint patterns.
- **Algorithm**: `sklearn.ensemble.IsolationForest`.
- **Flow**:
  1. Polls the `audit_logs` table.
  2. Builds decision trees on features: login concurrency, download size spikes, and geofence coordinate variances.
  3. Updates log rows with a computed anomaly score between `0.0` and `1.0`.

### `rete_match.py` (Rete Placement Script)
- **Role**: Evaluates rules and facts to map placements.
- **Flow**:
  1. Accepts JSON inputs containing student credentials and office rules.
  2. Executes pattern matching on tech skills, soft skills, major, and interview recommendations.
  3. Outputs matching departments with a calculated confidence indicator and score percentage.
