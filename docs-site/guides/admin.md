# Administrator Guide

Administrators oversee SmarTern configuration, user directory management, and system security controls.

---

## Account Management

The **Admin Dashboard** allows creating and filtering system accounts:
- **Filters**: Quickly filter the database by Role (Intern, Supervisor, Coordinator) or Department/Office (e.g. ICT, HR, Accounting).
- **Audit Logs**: View chronological event streams tracking admin and user activities.

---

## Bulk CSV Imports

To scale onboarding for new internship cycles, admins can bulk import accounts using spreadsheets or CSV files.

### Import Steps
1. Navigate to **Account Management > Bulk Import**.
2. Click **Download CSV Template** to fetch the formatted skeleton.
3. Fill in required fields: `First Name`, `Middle Name`, `Last Name`, `School`, `Email`, `Gender`, `Phone Number` (+63 format).
4. Upload the completed file. The system processes rows in the background using the queue listener, automatically generating secure passwords and queuing welcome emails.

---

## Geofencing Configuration

Before interns can record attendance, their deployed offices must be registered:
1. Navigate to the **Geofencing Module**.
2. Select or create an **Office / Department**.
3. Input the street address, precise Latitude, Longitude, and a allowed **Radius** (in meters).
4. Save the geofence to activate location enforcement for interns assigned to this office.

---

## System Backups

- **Automated**: The system runs an automated full database and storage backup daily at **7:00 PM**.
- **Manual**: Admins can trigger a manual backup from the Settings panel at any time before performing updates or critical system tasks.
