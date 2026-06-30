# Environment Configuration

SmarTern reads configuration settings from the `.env` file at the root of the project directory.

## Core Settings

- `APP_NAME`: Name of the application (default: `SmarTern`).
- `APP_ENV`: Environment type (`local`, `production`).
- `APP_KEY`: Application encryption key generated via `php artisan key:generate`.
- `APP_DEBUG`: Enables detailed stack traces (`true` in development, `false` in production).
- `APP_URL`: Base URL of the web server. Must match your local domain or ngrok public link.

---

## Database

Configures database parameters for Eloquent model persistence:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartern
DB_USERNAME=root
DB_PASSWORD=
```

---

## Real-time & Broadcasting (Reverb / Echo)

Broadcasting driver for WebSockets and real-time events:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME="http"
```

---

## Multi-Factor Authentication (MFA)

Settings for Google2FA behavior:
- `OTP_WINDOW`: Buffer window for TOTP synchronization (default: `1` corresponding to 30 seconds).

---

## Machine Learning Daemon

Paths and config for the Python Isolation Forest daemon:
- `PYTHON_BINARY`: Command/path to run Python (e.g. `python` or `python3`).
- `IF_DAEMON_INTERVAL`: Polling interval in seconds for log scanning.
