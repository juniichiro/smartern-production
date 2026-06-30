# Anomaly Detection (Isolation Forest)

SmarTern secures its internship lifecycle using machine learning-driven anomaly detection to identify fraud, integrity bypasses, or compromised accounts.

---

## Machine Learning Engine: Isolation Forest

The system implements the **Isolation Forest** unsupervised learning algorithm to isolate outliers in user activity. Instead of profiling normal data, Isolation Forest explicitly isolates anomalies by building random decision trees. Because anomalies require fewer splits to isolate, they appear closer to the root of the tree, producing a higher anomaly score.

---

## The Anomaly Daemon (`if_daemon.py`)

A persistent Python daemon runs in the background (`scripts/if_daemon.py`) to process system audit logs.

### Detection Triggers
The model evaluates three main anomaly categories:
1. **Geolocation Outliers**: Clock-in coordinates that differ dramatically from the designated office geofence or show impossible travel speeds between consecutive events.
2. **Authentication Anomalies**: Simultaneous logins on different devices, rapid session switches, or excessive failed logins before lockout.
3. **Behavioral Spikes**: Extreme counts of document uploads, downloads, or unexpected access to privileged endpoints.

---

## Scoring Tiers

Logs are evaluated and categorized into three risk levels:

| Score Range | Severity | Action Triggered |
|---|---|---|
| **`0.0` - `0.5`** | **Normal** | Logged normally, no action taken. |
| **`0.51` - `0.7`** | **Warning** | Flashed on the administrator's dashboard warning stream. |
| **`0.71` - `1.0`** | **Critical** | Triggers an immediate system notification and email alert to administrators. |

---

## Precautionary Remediation

When a critical anomaly score is recorded, administrators have options to:
- Generate and download a comprehensive Isolation Forest anomaly report.
- Temporarily restrict/suspend the flagged account directly from the dashboard.
- Force immediate session termination and password reset.
