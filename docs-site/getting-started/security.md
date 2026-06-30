# MFA & Geofencing Security Architecture

This section details the low-level implementation of SmarTern's authentication and location verification security protocols.

---

## 1. Multi-Factor Authentication (MFA) Implementation

SmarTern implements RFC 6238 Time-Based One-Time Password (TOTP) parameters using the `pragmarx/google2fa-laravel` package.

### Database Attributes
Each user's MFA state is stored directly in the `users` table:
- `google2fa_secret`: Encrypted string containing the user's base32 seed.
- `google2fa_enabled`: Boolean flag indicating configuration completion status.

### Authenticator Synchronization & Verification Loop
When a user submits their 6-digit verification code, the system validates it against their seed:
1. **Window Size Buffer**: Configured to `1` (via the environment parameter `OTP_WINDOW`), which permits a drift of $\pm 30$ seconds (covering the previous, current, and next time windows) to account for slight client-server clock desynchronization.
2. **UI Synchronization Timer**: Built in Alpine.js on the frontend, it syncs with the client system clock to show a countdown progress bar:
   ```javascript
   // Alpine.js clock timer logic
   init() {
       setInterval(() => {
           this.secondsLeft = 30 - (Math.floor(Date.now() / 1000) % 30);
       }, 1000);
   }
   ```

### Replay Attack Prevention (Code Burn)
To prevent replay attacks within the 30-second window:
- When a code is validated successfully, the timestamp window index (`Math.floor(timestamp / 30)`) is stored in a database cache mapped to the user.
- Any subsequent validation attempt with a code belonging to the same window index is immediately rejected, ensuring single-use execution ("code burn").

---

## 2. GPS Geofencing & Coordinates Integrity

Attendance logging routes verify that the intern is physically within the office's geofenced boundaries before recording clock-in/out timestamps.

### The Haversine Mathematical Model
SmarTern calculates the orthodromic distance (great-circle distance) between the intern's device coordinates and the office coordinates using the **Haversine Formula**:

```
d = 2 * r * arcsin( √[ sin²(Δφ / 2) + cos(φ1) * cos(φ2) * sin²(Δλ / 2) ] )
```

Where:
- **`φ1`, `φ2`** are the latitudes of point 1 and point 2 (in radians).
- **`Δφ`** is the difference in latitude.
- **`Δλ`** is the difference in longitude.
- **`r`** is the Earth's radius (mean radius = `6,371,000` meters).

### PHP Geospatial Service Implementation (`AttendanceService.php`)
```php
public function calculateDistance($lat1, $lon1, $lat2, $lon2): float
{
    $earthRadius = 6371000; // in meters

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // distance in meters
}
```

### Execution Flow
```
Intern Client              Laravel Controller          Office Model
    │                              │                        │
    │ 1. Request clock-in          │                        │
    ├─────────────────────────────►│                        │
    │    (Lat, Lon credentials)    │ 2. Fetch office coords │
    │                              ├───────────────────────►│
    │                              │    (Lat, Lon, Radius)  │
    │                              │◄───────────────────────┤
    │                              │                        │
    │                              │ 3. Run Haversine check │
    │                              │    Distance <= Radius? │
    │                              │                        │
    │◄─────────────────────────────┤                        │
    │ 4. Allow log OR Abort 422    │                        │
```
- **Radius Parameter**: Configured on a per-office basis in meters (typically `50m` to `100m`).
- **Location Spoofing Guard**: The controller inspects the geolocation header and enforces verification. Bypasses or abnormal distances are instantly marked as warnings for the Isolation Forest detector.
