<p align="center">
  <img src="public/images/blue-smartern.png" alt="SmarTern Logo" width="300">
</p>

# SmarTern: Internship Management System

**SmarTern** is a specialized internship management platform developed for the **Schools Division Office of Quezon City (SDO QC)**. The system is designed to digitalize and automate the entire internship lifecycle, providing a seamless experience for interns, supervisors, and school coordinators.

## System Overview

SmarTern serves as a central hub for managing student deployments, tracking daily accomplishments, and monitoring attendance with geographic and integrity checks. It transforms traditional paper-based processes into a data-driven digital ecosystem.

### Machine Learning Integration

The system leverages advanced algorithms to enhance decision-making and security:

*   **Rete Matching Algorithm:** An efficient pattern-matching algorithm used in the **Automated Office Placement** module. It evaluates intern skills, academic backgrounds, and preferences against a set of institutional rules to ensure the most compatible office assignments. Supports high-performance batch processing.
*   **Isolation Forest Algorithm:** A machine learning model used for **Anomaly Detection**. It monitors attendance logs and system activities via a **persistent Python daemon** (`if_daemon.py`) to identify outliers or irregular patterns, helping supervisors detect potential policy violations or fraudulent behavior with near-zero latency.

### Security Features

*   **Standard & Custom MFA:** Enhances account security by supporting standard authenticator apps (**Google Authenticator**, **Microsoft Authenticator**) via TOTP. 
    *   **Mandatory Setup:** New users are forced to configure MFA on their first login.
    *   **Single-Use Logic:** Implements "Code Burn" security where every 6-digit code can only be used once.
    *   **UI Synchronization:** Verification page features a countdown timer perfectly synced with the authenticator app's 30-second window.
    *   **Scan-to-View (Optional):** A mobile-friendly convenience flow for viewing codes via QR scanning.
*   **Geofencing & GPS Integrity:** The attendance module verifies that interns are physically present within their assigned office's radius before allowing clock-in/out.
*   **Security Analysis Middleware:** Displays a modern skeleton loading UI while background machine learning analysis is performed, providing an "app-like" responsive feel.

---

## Installation & Setup

Follow these steps to set up the development environment on your local machine.

### 1. Clone the Repository
```bash
git clone https://github.com/juniichiro/SmarTern
cd SmarTern
```

### 2. Install Backend Dependencies
```bash
composer install
```

### 3. Install Frontend Dependencies
```bash
npm install
```

### 4. Configure Environment
```bash
cp .env.example .env
# Update .env with your local database credentials
php artisan key:generate
```

### 5. Initialize Database
```bash
php artisan migrate:fresh --seed
```

### 6. Setup Python Environment (for ML Features)
Ensure you have Python installed, then install the required libraries:
```bash
pip install pandas scikit-learn numpy
```

### 7. Run the Application
To start the development environment, you need to run the following:

**A. Start the Laravel/Vite development stack:**
```bash
npm run dev
```

**B. ML Daemon (Optional):**
The system will attempt to auto-start the Python daemon (`scripts/if_daemon.py`) via the `AnomalyDetectionService` when needed.

---

## 🌐 External Testing (via ngrok)

Since the **Attendance Module** and **MFA** features require a mobile device to scan QR codes, you must expose your local server to the internet using **ngrok**.

### 1. Launch ngrok
Expose the default Laravel port (8000):
```bash
ngrok http 8000
```

### 2. Update .env Configuration
Copy the **Forwarding URL** provided by ngrok (e.g., `https://random-id.ngrok-free.app`) and update your `.env` file:
```env
APP_URL=https://random-id.ngrok-free.app
```

---

## 📂 System Structure

```text
SmarTern/
├── app/
│   ├── Console/Commands/
│   │   ├── RecalculateAnomalyScores.php
│   │   ├── RestoreBackup.php
│   │   └── RunBackup.php
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── AccountController.php
│   │   │   ├── AdminController.php
│   │   │   └── SecurityController.php
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php
│   │   │   ├── ConfirmablePasswordController.php
│   │   │   ├── EmailVerificationNotificationController.php
│   │   │   ├── EmailVerificationPromptController.php
│   │   │   ├── NewPasswordController.php
│   │   │   ├── PasswordController.php
│   │   │   ├── PasswordResetLinkController.php
│   │   │   ├── PasswordUpdateController.php
│   │   │   ├── RegisteredUserController.php
│   │   │   └── VerifyEmailController.php
│   │   ├── Coordinator/
│   │   │   ├── DashboardController.php
│   │   │   └── InternController.php
│   │   ├── HeadSupervisor/
│   │   │   ├── AnalyticsController.php
│   │   │   ├── CertificateController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── InternManagementController.php
│   │   │   ├── OfficeManagementController.php
│   │   │   ├── OfficeMatchingController.php
│   │   │   ├── OnboardingManagementController.php
│   │   │   └── ReportController.php
│   │   ├── Intern/
│   │   │   ├── AttendanceController.php
│   │   │   ├── CertificateController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfileController.php
│   │   │   └── ProgressController.php
│   │   ├── Supervisor/
│   │   │   ├── AttendanceController.php
│   │   │   ├── CertificateController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── InternController.php
│   │   │   ├── ReportController.php
│   │   │   └── TaskController.php
│   │   ├── Controller.php
│   │   ├── MFAController.php
│   │   ├── NotificationController.php
│   │   ├── OnboardingController.php
│   │   └── ProfileController.php
│   ├── Middleware/
│   │   ├── ForcePasswordChange.php
│   │   ├── MFACheck.php
│   │   ├── RoleMiddleware.php
│   │   └── SecurityAnalysisMiddleware.php
│   ├── Models/
│   │   ├── Announcement.php
│   │   ├── Attendance.php
│   │   ├── AuditLog.php
│   │   ├── Certificate.php
│   │   ├── InternDetail.php
│   │   ├── Journal.php
│   │   ├── MatchResult.php
│   │   ├── Notification.php
│   │   ├── Office.php
│   │   ├── OfficeRule.php
│   │   ├── Role.php
│   │   ├── SchoolCoordinatorDetail.php
│   │   ├── Submission.php
│   │   ├── SupervisorDetail.php
│   │   ├── SystemBackup.php
│   │   ├── Task.php
│   │   └── User.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── Services/
│   │   ├── AnomalyDetectionService.php
│   │   └── GeofenceService.php
│   └── View/Components/
│       ├── AppLayout.php
│       ├── GuestLayout.php
│       ├── notification-sidebar.php
│       └── sidebar.php (and role-specific links)
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_02_27_032854_create_roles_table.php
│   │   ├── 2026_02_27_033222_create_offices_table.php
│   │   ├── 2026_02_27_033223_create_users_table.php
│   │   ├── 2026_02_27_033342_create_intern_details_table.php
│   │   ├── 2026_02_27_033905_create_supervisor_details_table.php
│   │   ├── 2026_02_27_034140_create_school_coordinator_details_table.php
│   │   ├── 2026_03_01_095755_create_office_rules_table.php
│   │   ├── 2026_03_01_102101_create_match_results_table.php
│   │   ├── 2026_04_19_053736_create_tasks_table.php
│   │   ├── 2026_04_19_053737_create_submissions_table.php
│   │   ├── 2026_04_19_054747_create_attendances_table.php
│   │   ├── 2026_04_19_054748_create_journals_table.php
│   │   ├── 2026_04_19_054934_create_certificates_table.php
│   │   ├── 2026_04_19_055109_create_notifications_table.php
│   │   ├── 2026_04_19_055110_create_announcements_table.php
│   │   ├── 2026_04_19_055519_create_audit_logs_table.php
│   │   └── 2026_04_19_055730_create_system_backups_table.php
│   └── seeders/
│       ├── AuditLogSeeder.php
│       ├── DatabaseSeeder.php
│       ├── InternDetailSeeder.php
│       ├── OfficeRuleSeeder.php
│       ├── OfficeSeeder.php
│       ├── RoleSeeder.php
│       ├── SchoolCoordinatorDetailSeeder.php
│       ├── SupervisorDetailSeeder.php
│       └── UserSeeder.php
├── resources/views/
│   ├── admin/ (accounts, config, dashboard, offices, security)
│   ├── auth/ (login, register, passwords, verification, mfa_verify, mfa_scan_code, skeleton-loading)
│   ├── coordinator/ (dashboard, interns, certificates)
│   ├── head-supervisor/ (analytics, matching, reports, onboarding)
│   ├── intern/ (attendance, dashboard, profile, progress, tasks)
│   ├── supervisor/ (attendance, dashboard, interns, reports, tasks)
│   ├── components/ (UI elements, sidebar, buttons, inputs)
│   ├── exports/ (accomplishments, analytics, reports PDFs)
│   ├── layouts/ (app.blade.php)
│   └── onboarding/ (form.blade.php)
├── scripts/
│   ├── if_daemon.py (Persistent ML Worker)
│   ├── isolation_forest.py
│   ├── rete_match.py (Batch Matching Engine)
│   └── test_python.py
└── .env.example
```

---

*Developed for SDO Quezon City.*
