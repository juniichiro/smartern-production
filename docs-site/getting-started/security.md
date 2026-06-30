# Security & MFA

SmarTern implements strict security procedures to protect the integrity of student deployments and attendance.

## Multi-Factor Authentication (MFA)

To satisfy SDO QC data protection guidelines, all users must configure Multi-Factor Authentication.

### Setup Flow
1. **Mandatory Configuration**: New accounts are redirected to the MFA setup page on their first login. Access to dashboards is blocked until MFA is configured.
2. **TOTP QR Code**: SmarTern generates a QR code compatible with standard authenticator applications (Google Authenticator, Microsoft Authenticator).
3. **Countdown Synchronization**: The MFA code entry page features a dynamic visual progress countdown synchronized with the standard 30-second TOTP window, ensuring users know when their code is expiring.
4. **Code Burn**: To prevent replay attacks, each 6-digit verification code is "burned" (flagged as used) instantly and can never be reused.

---

## GPS Geofencing & Location Integrity

Attendance integrity is enforced using browser-based GPS geofencing.

- **Office Coordinates**: Every office profile includes designated latitude, longitude, and an acceptable radius (e.g., 50 meters).
- **Location Validation**: When an intern clocks in or out, the system requests their device GPS coordinates.
- **Out of Bounds**: If the distance between the intern's location and their assigned office coordinates exceeds the configured radius, the clock-in/out attempt is blocked, and a warning is logged.

---

## Real-Time Security Analysis

- **Verification Middleware**: System operations run through security middleware that monitors user patterns.
- **Skeleton UX**: To keep the interface highly responsive, critical actions present a modern skeleton loading state while the background ML pipeline evaluates the transaction.
