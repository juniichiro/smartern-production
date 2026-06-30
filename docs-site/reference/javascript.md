# AlpineJS & Javascript Reference

This document details the frontend JavaScript structure, Alpine.js components, and real-time broadcasting listeners.

---

## 1. Bootstrapping & Client Services

SmarTern compiles scripts via Vite. Entry points are located in `resources/js/`.

### `bootstrap.js` & `echo.js`
- **Axios Configuration**: Configures standard HTTP headers (`X-Requested-With`) to handle asynchronous AJAX queries securely.
- **WebSocket Connection**: Bootstraps the **Laravel Echo** client with the Pusher protocol to connect with the **Laravel Reverb** server:
  ```javascript
  import Echo from 'laravel-echo';
  import Pusher from 'pusher-js';

  window.Pusher = Pusher;
  window.Echo = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: import.meta.env.VITE_REVERB_PORT,
      forceTLS: false,
      enabledTransports: ['ws', 'wss'],
  });
  ```

### `session-check.js`
- **Role**: Tracks user inactivity on dashboards.
- **Behavior**: Sets a countdown timer matching your `config/session.php` limit (30 minutes). If no interactions (mouse moves, keystrokes) are recorded within 30 minutes, it automatically prompts session logouts to secure the terminal.

---

## 2. Alpine.js Components Catalog (`Alpine.data`)

These components manage dynamic UI interactions without requiring heavy frameworks.

### `attendanceManager`
- **Path**: `resources/js/app.js` (Attendance clock view)
- **Role**: Drives the GPS Geofencing clock-in process.
- **Verification Flow**:
  1. Requests device location via the Web Geolocation API:
     ```javascript
     navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
     ```
  2. If the user accepts, retrieves latitude and longitude coordinates.
  3. Sends an AJAX POST request containing the coordinates to the Laravel endpoint.
  4. If coordinates are out of bounds, catches the `422` validation exception and dispatches warning toast messages.

### `mfaTimer`
- **Path**: `resources/js/app.js` (MFA Setup/Login screens)
- **Role**: Counts down the 30-second window in sync with authenticator apps.
- **Behavior**: Triggers window page reloads when the 30-second TOTP index expires, forcing a new code refresh.

### `notificationManager`
- **Role**: Subscribes to real-time WebSockets.
- **Behavior**:
  - Joins a private channel mapped to the logged-in user:
    ```javascript
    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            // Push notification to UI array and trigger audio chime
        });
    ```
  - Listens for new task uploads, evaluation outcomes, and anomaly warnings.

### `darkModeManager`
- **Role**: Toggles class attributes on the root HTML element.
- **Behavior**: Watches changes to the theme toggle button, writes state values to `localStorage`, and updates classes (`dark`).

### `toastManager`
- **Role**: Global alert queues.
- **Behavior**: Listens for window-level custom events (`toast`) to enqueue notification structures. Handles fades and duplicate suppression.
