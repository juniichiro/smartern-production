# Blade Views & Layouts Reference

This document provides a detailed catalog of the Laravel Blade views and layout templates located in the `resources/views/` directory.

---

## 1. Core Layout Files (`App/Views/Layouts`)

Laravel layouts act as parent wrappers, injecting standard configurations into child views.

### `layouts/app.blade.php`
- **Role**: Standard layout wrapper for all authenticated dashboard portals.
- **Key Operations**:
  - Enforces dark mode checks instantly on render to prevent light flashes.
  - Loads Google Fonts (`Outfit` and `Inter`) and Vite compiler assets (`app.css`, `app.js`).
  - Implements the session checker script (`session-check.js`) to trigger timeouts.
  - Renders the custom layout sidebar component (`x-sidebar`) and the global notifications drawer (`x-notification-sidebar`).

### `layouts/guest.blade.php`
- **Role**: Minimal layout wrapper for unauthenticated views (e.g. login screens, MFA configuration, and error pages).

---

## 2. Reusable UI Components (`App/Views/Components`)

These custom Blade components (called via `<x-name />`) ensure UI consistency and reduce duplicate code.

| Component name | Syntax | Purpose |
|---|---|---|
| **`avatar`** | `<x-avatar :user="$user" />` | Displays the user's formal profile image or falls back to a colored initials circle. |
| **`card-container`** | `<x-card-container>` | Standard flex dashboard card with dark mode borders. |
| **`stat-card`** | `<x-stat-card>` | Displays high-contrast metrics, indicators, and links (e.g., Anomaly counter card). |
| **`page-layout`** | `<x-page-layout>` | Orchestrates the top navigation layout, sidebar position, and slots. |
| **`toast`** | `<x-toast>` | Frontend element listening for dispatch triggers to animate success/error popups. |
| **`skeleton`** | `<x-skeleton>` | Renders loading blocks for background ML processing screens. |

---

## 3. Dashboard Portal Views

Views are grouped into subdirectory modules matching specific user roles.

### Admin Portal (`resources/views/admin/`)
- **`dashboard.blade.php`**: Consolidates running status indicators for services, recent accounts list, and anomaly metric flags.
- **`accounts.blade.php`**: Orchestrates account listings, role filters, and modal inputs for CSV student imports.
- **`configuration.blade.php`**: Registers SDO QC office parameters and handles backup tables.
- **`security-monitor.blade.php`**: Log monitor console with user controls for marking records safe or suspending accounts.

### Supervisor Portal (`resources/views/supervisor/`)
- **`dashboard.blade.php`**: Local department head view displaying intern attendance counts and completed tasks.
- **`tasks.blade.php`**: Dashboard displaying active deliverables, progress sheets, and submission files.
- **`announcements.blade.php`**: Broadcast announcement configuration forms.

### Head Supervisor Portal (`resources/views/head-supervisor/`)
- **`office-matching.blade.php`**: The Rete placement dashboard containing candidate listings, interview evaluations, recommended matches, and the "Confirm Deployment" finalization button.

### Intern Portal (`resources/views/intern/`)
- **`attendance.blade.php`**: Renders the clock-in/out terminal. Triggers browser geolocation lookups and locks clock actions when out of geofenced coordinates.
- **`progress.blade.php`**: Renders active assignments, submission upload elements, and logs.
