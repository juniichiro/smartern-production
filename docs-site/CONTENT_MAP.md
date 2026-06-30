# SmarTern Documentation Content Map

This map outlines the structure of the SmarTern documentation site, tracking each page's path, purpose, and primary source of truth within the codebase.

## Navigation Structure & Pages

### 1. Getting Started
- **Introduction** (`/index.md`)
  - *Purpose*: Homepage and system pitch (MFA, Rete placement, Isolation Forest anomaly detection).
  - *Sources*: `README.md`, `.agents/SmarTern_Requirements.md`
- **Installation & Setup** (`/getting-started/installation.md`)
  - *Purpose*: Step-by-step setup guides for Composer (Laravel), npm (Vite), and pip (Python).
  - *Sources*: `README.md`
- **Security & MFA** (`/getting-started/security.md`)
  - *Purpose*: Overview of TOTP MFA, code-burn logic, and geofencing setup.
  - *Sources*: `README.md`, `app/Services/Google2FAService.php`, `.agents/added_requirements.md`

### 2. Core Concepts
- **System Architecture** (`/core-concepts/architecture.md`)
  - *Purpose*: High-level view of Laravel 13, Alpine.js, Tailwind, and the Python ML Daemon integration.
  - *Sources*: Codebase structure, `vite.config.js`, `composer.json`
- **Automated Office Placement (Rete)** (`/core-concepts/rete-matching.md`)
  - *Purpose*: Technical explanation of the Rete Algorithm used to place interns in offices.
  - *Sources*: `README.md`, `app/Services/ReteMatchingService.php` (or placement logic)
- **Anomaly Detection (Isolation Forest)** (`/core-concepts/anomaly-detection.md`)
  - *Purpose*: Explanation of the Python daemon (`if_daemon.py`) tracking logins and anomalous activities.
  - *Sources*: `scripts/if_daemon.py`, `app/Services/AnomalyDetectionService.php`

### 3. User & Role Guides
- **Administrator Portal** (`/guides/admin.md`)
  - *Purpose*: How admins manage accounts, run bulk CSV imports, review audit logs, and trigger backups.
  - *Sources*: `.agents/SmarTern_Requirements.md`, CSV import controllers
- **Supervisors & Head Supervisors** (`/guides/supervisors.md`)
  - *Purpose*: Managing tasks, announcements, viewing analytics, and using the Rete placement module.
  - *Sources*: `.agents/SmarTern_Requirements.md`, `.agents/cross-role-dependencies.md`
- **Interns & School Coordinators** (`/guides/interns-coordinators.md`)
  - *Purpose*: Log daily accomplishments, attendance with GPS/geofencing, and coordinator overview features.
  - *Sources*: `.agents/SmarTern_Requirements.md`, Attendance controller logic

### 4. Technical Reference
- **Environment Configuration** (`/reference/environment.md`)
  - *Purpose*: Detailed breakdown of `.env` options (database, ngrok, Reverb/Echo, Python daemon paths).
  - *Sources*: `.env.example`, `config/` directory
- **Testing & Pest Suite** (`/reference/testing.md`)
  - *Purpose*: Running backend tests using the Pest framework.
  - *Sources*: `tests/`, `.agents/ATTENDANCE_TEST_SUITE.md`, `composer.json`
- **Controllers Catalog** (`/reference/controllers.md`)
  - *Purpose*: Exhaustive directory mapping of all Laravel controllers and exposed methods.
  - *Sources*: `app/Http/Controllers/`
- **Models & Schemas** (`/reference/models.md`)
  - *Purpose*: Detailed database fields, relationships, and constraints mappings for all Eloquent models.
  - *Sources*: `app/Models/`
- **Middlewares & Services** (`/reference/middleware-services.md`)
  - *Purpose*: Technical breakdowns of middlewares, class parameters, and application service logic.
  - *Sources*: `app/Http/Middleware/`, `app/Services/`
- **Routes & Python Scripts** (`/reference/routes-scripts.md`)
  - *Purpose*: Direct catalog of route groups, middlewares, and stand-alone python daemons.
  - *Sources*: `routes/web.php`, `scripts/`
- **Artisan Console Commands** (`/reference/console-commands.md`)
  - *Purpose*: Technical directory of custom command signatures, arguments, scheduler options, and cron parameters.
  - *Sources*: `app/Console/Commands/`, `routes/console.php`
- **Blade Views & Layouts** (`/reference/views.md`)
  - *Purpose*: Renders UI elements, reusable components, layouts, and portal files directory mapping.
  - *Sources*: `resources/views/`
- **AlpineJS & Javascript** (`/reference/javascript.md`)
  - *Purpose*: Client side scripts, Alpine components catalog, Echo WebSockets setup, and session management.
  - *Sources*: `resources/js/`
