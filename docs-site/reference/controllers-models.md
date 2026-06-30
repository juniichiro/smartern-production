# Database Schemas & Controller Catalog

This reference lists the database structures, core model classes, and controller methods implementing SmarTern.

---

## 1. Database Table Schemas & Model Mappings

Every database table is managed via Laravel Eloquent models.

### `users` Table
Stores login credentials and security states.
- **`id`**: `bigint` (Primary Key, Auto Increment)
- **`email`**: `string` (Unique index)
- **`password`**: `string` (Minimum 12 chars check)
- **`role_id`**: `integer` (Foreign key to `roles.id`)
- **`google2fa_secret`**: `text` (Encrypted base32 TOTP secret)
- **`google2fa_enabled`**: `boolean` (Default: `false`)
- **`status`**: `enum` (`active`, `suspended`, `onboarding`)
- **`last_login_at`**: `timestamp` (Used for time_delta features)

### `intern_details` Table
Extends the `users` profile for student interns.
- **`id`**: `bigint` (Primary Key)
- **`user_id`**: `bigint` (Foreign key to `users.id` with `cascadeOnDelete()`)
- **`office_id`**: `bigint` (Foreign key to `offices.id` or `null` before matching)
- **`first_name`**, **`middle_name`**, **`last_name`**: `string`
- **`school`**: `string`
- **`gender`**: `string` (Stores male, female, others)
- **`phone_number`**: `string` (Enforced to +63 format)
- **`avatar_path`**: `string` (Path to formal 1x1 image)
- **`emergency_contact_name`**: `string`
- **`emergency_contact_phone`**: `string`

### `attendance` Table
Tracks daily login and geospatial validations.
- **`id`**: `bigint` (Primary Key)
- **`user_id`**: `bigint` (Foreign key to `users.id`)
- **`office_id`**: `bigint` (Foreign key to `offices.id`)
- **`clock_in_at`**: `timestamp`
- **`clock_out_at`**: `timestamp` (Nullable)
- **`clock_in_lat`**, **`clock_in_lon`**: `double` (Recorded coords)
- **`clock_out_lat`**, **`clock_out_lon`**: `double` (Nullable)
- **`is_geofence_bypassed`**: `boolean` (Flagged if forced bypass occurs)

### `audit_logs` Table
Scanned by the Python ML daemon.
- **`id`**: `bigint` (Primary Key)
- **`user_id`**: `bigint` (Foreign key to `users.id` or `null` for guests)
- **`action`**: `string` (e.g., `login`, `download`, `clock_in`)
- **`ip_address`**: `string`
- **`user_agent`**: `string`
- **`payload`**: `json` (Details of the action)
- **`processed`**: `boolean` (Default: `false`, set to `true` by ML daemon)
- **`anomaly_score`**: `float` (Output score, default: `0.0`)
- **`created_at`**: `timestamp`

---

## 2. Controller Routing & Methods Catalog

Below is the directory mapping of route controllers and their functions.

```
app/Http/Controllers/
├── Admin/
│   ├── AccountController.php     --> store(), update(), destroy(), importBulkCSV()
│   ├── AdminController.php       --> configuration(), offices(), triggerManualBackup()
│   └── SecurityController.php    --> index(), markCompromised(), markSuspended()
├── Supervisor/
│   ├── TaskController.php        --> store(), updateStatus(), destroy()
│   ├── AnnouncementController.php--> store(), broadcast()
│   └── OfficeManagementController.php --> updateRequirements(), updateRules()
└── HeadSupervisor/
    ├── OfficeMatchingController.php --> index(), storeEvaluation(), finalizeMatches()
    └── AnalyticsController.php   --> getMultiOfficeMetrics()
```

### Route Parameter Restrictions
- **`Admin\AccountController@importBulkCSV`**: Accepts a single `file` parameter under standard validation. Dispatches processing jobs onto the database queue queue listener.
- **`HeadSupervisor\OfficeMatchingController@storeEvaluation`**: Triggers Rete Matching re-runs for the specific student instantly:
  ```php
  $this->matchingService->runMatchingForApplicant($applicant);
  ```
- **`Supervisor\TaskController@updateStatus`**: Accepts states: `completed`, `pending_revision`, `overdue`. Logs state change audit entries instantly.
- **`Admin\AdminController@triggerManualBackup`**: Creates compressed tar archive files of the database and `storage/app` paths, placing them into `storage/app/backups/`.
