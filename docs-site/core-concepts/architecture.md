# System Architecture

SmarTern combines a robust PHP web application layer with a specialized Python machine learning daemon to deliver real-time security analysis and intelligent office placement.

```
┌────────────────────────────────────────────────────────┐
│                      Client Browser                    │
│          (Blade Templates, Tailwind, Alpine.js)        │
└───────────┬────────────────────────────────┬───────────┘
            │ Request / Response             │ WebSockets / Live updates
            ▼                                ▼
┌───────────────────────┐        ┌───────────────────────┐
│     Laravel App       │◄──────►│    Laravel Echo       │
│  (Controllers, Eloq)  │        │   (Broadcasting/Reverb)│
└───────────┬───────────┘        └───────────────────────┘
            │ Writes log events
            ▼
┌───────────────────────┐        ┌───────────────────────┐
│       Database        │◄──────►│   Python ML Daemon    │
│    (MySQL/SQLite)     │        │  (if_daemon.py)       │
└───────────────────────┘        └───────────────────────┘
```

## Backend Framework (Laravel 13)

The backend handles core business logic, user authorization, and data persistence:
- **Routing & Controllers**: Standard Laravel controllers serving views and managing form requests.
- **Eloquent ORM**: Strong relationship mapping between Users, Roles, Offices, Interns, Attendance logs, and Audit logs.
- **Queue System**: Handles heavier operations (such as processing large CSV batch student imports) in the background.
- **Real-time Events**: Leverages Laravel Reverb/Echo for pushing instantaneous dashboard alerts.

---

## Frontend Layer (Vite, Alpine.js, Tailwind)

- **Vite**: Bundles application scripts and CSS assets with hot-module replacement.
- **Alpine.js**: Provides responsive frontend logic (e.g. countdown timers, sidebar toggles, and modal states) without loading heavy JS frameworks.
- **Tailwind CSS v3**: Delivers utility-first styling with custom brand constraints defined in `tailwind.config.js`.

---

## Machine Learning Integration

- **Anomaly Detection Daemon**: A persistent Python process (`scripts/if_daemon.py`) that monitors the database audit logs.
- **Rete Placement Engine**: Evaluation logic within PHP that maps intern skills against office requirements using a rule-based algorithm.
