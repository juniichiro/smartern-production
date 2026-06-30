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
1. **Data Ingestion**: The daemon pulls pending logs and extracts the numerical feature matrix `X`.
2. **Redundancy Reduction (Feng & Zhang, 2023)**:
   - To reduce overlapping tree bias, depth matrices are calculated across all decision trees:
     ```python
     corr = np.corrcoef(depths, rowvar=False)
     ```
   - Standard pruning removes any tree exhibiting a depth correlation coefficient > 0.90 from the forest ensemble.
3. **Anomaly Scoring**:
   - Calculates the negative of scikit-learn's sample scores to translate it to a standard positive range:
     ```python
     anomaly_score = -model.score_samples(X)
     ```
   - **Dynamic Baseline Correction**: Computes the average normal score from the bottom 50% (least anomalous) logs. Whitelisted entries (e.g. system loops) are automatically overridden to this normal baseline.
4. **Trigger & Action**:
   - If the resulting score is >= 0.7, the daemon flags the record as `critical` in the database and triggers real-time alerts.
