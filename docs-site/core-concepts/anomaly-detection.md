# Anomaly Detection (Isolation Forest)

This section covers the machine learning implementation for detecting system irregularities, centering on the Isolation Forest daemon architecture.

---

## 1. Feature Vector Formulation

The background python daemon `scripts/if_daemon.py` queries `audit_logs` periodically, compiling log rows into numerical feature matrices for the model.

```
       Audit Logs Database Table
                  │
                  ▼
         Feature Extraction:
 ┌──────────────────────────────────────┐
 │ - Lat/Lon coordinate delta           │
 │ - Failed login attempts count (30m)  │
 │ - Session switch speed (sec)         │
 │ - Bulk action / download indicators  │
 └──────────────────┬───────────────────┘
                    ▼
          Feature Vector array
   x = [coord_delta, logins, speed, size]
```

### Numerical Vector Variables
1. **`coordinate_delta`**: Geodetic distance in kilometers between the user's current IP/GPS entry and their previous log entry.
2. **`login_failures`**: Integer count of failed authentication attempts within the last 30 minutes.
3. **`time_delta`**: Time difference in seconds between consecutive logins (detecting impossible travel or session hijacking).
4. **`activity_volume`**: Cumulative rate of file downloads, uploads, or critical database mutations within a rolling 5-minute window.

---

## 2. Model Configuration & Hyperparameters

The model is instantiated using `scikit-learn`'s `IsolationForest` estimator:

```python
from sklearn.ensemble import IsolationForest

# Hyperparameter Setup
clf = IsolationForest(
    n_estimators=100,      # Number of decision trees to construct
    max_samples='auto',    # Sub-sample size: min(256, n_samples)
    contamination=0.05,    # Estimated proportion of anomalies in data (5%)
    random_state=42,       # Seed for reproducible splits
    n_jobs=-1              # Use all CPU cores for parallel tree building
)
```

### Tree Split Criteria
- Normal events require many splits (deep nodes in the trees) to isolate because they cluster closely.
- Outliers are isolated with very few splits (near the root of the trees) because their feature values fall in sparse domains.

---

## 3. Scoring Mathematics & Logging Lifecycle

The raw anomaly score `s(x)` for a feature vector `x` is defined as:

```
s(x) = 2 ^ ( - E(h(x)) / c(n) )
```

Where:
- **`h(x)`** is the path length of vector `x` in a decision tree.
- **`E(h(x))`** is the average path length of `h(x)` across all trees in the forest.
- **`c(n)`** is the average path length of an unsuccessful search in a Binary Search Tree built with `n` nodes.

### Score Translation
- **`s(x) → 1`**: Path length is very short. Classified as highly anomalous.
- **`s(x) → 0`**: Path length is long. Classified as normal.

### System Hook & Daemon Lifecycle
1. **Data Polling**: The daemon queries the database for raw logs where `processed = 0`.
2. **Vector Scaling**: Standardizes features using a historical baseline dataset (`storage/app/audit_baseline.json`).
3. **Prediction**:
   - Scores the record.
   - If the score exceeds the **`0.7`** threshold, the daemon writes a critical log level, dispatches alerts via Laravel Echo/Reverb channels, and marks the event status as `critical` in the `audit_logs` table.
4. **Daemon Persistence**: Managed on EC2 via systemd or run as a background service:
   ```bash
   python scripts/if_daemon.py --interval 3600
   ```
   *(Checks and recalculates scores every hour).*
