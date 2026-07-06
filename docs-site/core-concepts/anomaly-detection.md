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
 │ - Global & Role Action Frequency     │
 │ - User Action Baseline Frequency     │
 │ - Subnet /24 & Subnet /16 Density    │
 │ - UA Global & User-Device Pairing    │
 │ - Working Hours Binary Flag          │
 └──────────────────┬───────────────────┘
                    ▼
           Feature Vector array
    x = [global, role, user, subnet_c, 
         subnet_b, ua, device, hours]
```

### Numerical Vector Variables
1. **Global Action**: The relative frequency of the action across the entire system.
2. **Role-Action**: The frequency of this action within the user's role (detects privilege escalations).
3. **User-Action**: The frequency of this action relative to the user's personal baseline history.
4. **Subnet /24**: The density frequency of the user's /24 IP subnet globally (provides network location tolerance).
5. **Subnet /16**: The density frequency of the user's /16 IP subnet globally (detects broad geographic jumps).
6. **User-Agent**: The frequency of the specific cleaned browser/OS signature globally.
7. **User-Device**: The consistency frequency of the specific user-device pairing signature.
8. **Working Hours**: Binary indicator of whether the action occurred inside standard office hours (08:00-18:00): `1.0` if true, `0.0` otherwise.

---

## 2. Model Configuration & Hyperparameters

The model is instantiated using `scikit-learn`'s `IsolationForest` estimator:

```python
from sklearn.ensemble import IsolationForest

# Hyperparameter Setup
clf = IsolationForest(
    n_estimators=100,      # Number of decision trees to construct
    max_samples='auto',    # Sub-sample size: min(256, n_samples)
    contamination='auto',  # Automatically determines optimal splits
    random_state=42,       # Seed for reproducible splits
    n_jobs=1               # Single-threaded loop execution in daemon
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

---

## 4. Cold-Start Mitigation & Baseline Warming

To prevent new user accounts from triggering false-positive anomaly warnings (due to having empty activity frequencies), SmarTern utilizes a baseline warming mechanism.

### Automated Warming on Login
When a user logs in for the first time or initiates a new session, the `CheckSingleSession` middleware automatically triggers baseline seeding:
- It checks if the user has baseline entries in `storage/app/audit_baseline.json`.
- If missing, it generates a mock sequence of 5 to 10 typical, safe actions for their specific role (e.g., Interns get `Clock In`, `Clock Out`, `Task Submitted`, etc.).
- The logs are attributed directly to the user's actual live IP address and browser User-Agent.

### Manual Overwrite Command
In scenarios where an Administrator needs to manually override or reset a user's baseline (e.g., after restoring access via the Reactivation Letter protocol), they can run:
```bash
php artisan app:warm-user-baseline {user_id} --ip={ip_address} --user-agent="{user_agent_string}"
```
This command bypasses the standard checks, clears out any outdated baseline records for the target user ID, and injects fresh, matching logs to ensure immediate compatibility with the user's new network or browser profile.
