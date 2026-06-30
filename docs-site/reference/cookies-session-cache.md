# Cookies, Sessions & Cache Reference

This document details the configuration, table structures, and storage keys implementing cookies, stateful sessions, and memory caching in SmarTern.

---

## 1. Stateful Sessions

SmarTern tracks authenticated user states across portal operations using Laravel's session driver.

### Session Configuration (`config/session.php`)
- **Driver**: Configured to `database` (controlled via `SESSION_DRIVER` in `.env`). This secures session states by storing them in a central database table rather than plaintext files or client-side storage.
- **Lifetime**: Configured to **30 minutes** (`SESSION_LIFETIME=30`).
- **Inactivity Timeout**: If a user is inactive, the frontend JavaScript (`session-check.js`) and backend middleware work together to expire the session and force redirection to the login portal.

### The `sessions` Database Table
```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
- **`id`**: Unique session key corresponding to the encrypted ID stored in the user's browser cookie.
- **`payload`**: Encrypted serialized state containing the authenticated user’s credential variables.

---

## 2. Browser Cookie Directives

Cookies verify request contexts and defend the client browser against cross-site scripting and request forgery exploits.

| Cookie Name | Scope / Flags | Purpose |
|---|---|---|
| **`smartern_session`** | `HttpOnly`, `Secure`, `SameSite=Lax` | Encrypted reference to the active row in the `sessions` database table. |
| **`XSRF-TOKEN`** | `Secure`, `SameSite=Lax` | Clear-text copy of the active session's CSRF token, read by Axios to populate request headers. |
| **`google2fa_otp_hold`** | `HttpOnly`, `Secure` | Temporary identifier holding the session context while the user undergoes the 2FA verify check. |

### Cookie Security Directives
- **`HttpOnly`**: Enforced on all session cookies to restrict access from client-side scripts, protecting keys from script-based extraction.
- **`Secure`**: Restricts cookie transmission to HTTPS connections only, preventing plaintext interception.
- **`SameSite=Lax`**: Limits cookie headers from being dispatched during cross-site requests, mitigating CSRF attack vectors.

---

## 3. Caching Architecture

Caching manages background queues, ML analysis locks, and anti-replay structures.

### Cache Stores (`config/cache.php`)
- **Driver**: Configured to use the `database` store (via `CACHE_STORE` in `.env`), compiling keys in the `cache` table.
- **Lock Connection**: Separate database connection thread handles table transaction locks during concurrent writes.

### Critical Cache Keys & Operations

#### 1. Machine Learning Recalculation Lock
- **Key**: `recalculate_anomaly_scores_lock`
- **Purpose**: A Mutex lock acquired by the scheduler when invoking `app:recalculate-anomaly-scores`. It expires in 60 seconds to ensure multiple cron runners cannot process anomaly logs concurrently.

#### 2. MFA Replay Protection (OTP Code Burn)
- **Key**: `users:{user_id}:otp:{time_window_index}`
- **Purpose**: Stores the time window index (e.g. current UNIX timestamp divided by 30) of a successfully validated 2FA login.
- **Expiration**: Retained for 60 seconds. Any login request with the same code inside this period is immediately blocked, protecting accounts from token capture/replay attacks.

#### 3. GPS Geofencing Coordinate Caches
- **Key**: `offices:{office_id}:coordinates`
- **Purpose**: Caches physical geofence coordinates and radius thresholds to speed up great-circle distance checks during high-frequency clock-in operations.

---

## 4. Redis Production Integration

In production environments, SmarTern can be configured to offload caching, session persistence, and background task queues onto **Redis** to improve page load latency and database throughput.

### Configuration Variables (`.env`)
To enable Redis drivers, update the following parameters:
```env
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Redis Key Partitioning & Serialization
Laravel automatically prefixes keys with the `database.redis.options.prefix` parameter (configured in `config/database.php` as `smartern_database_`) to prevent key space collisions on shared Redis clusters.
- **Session Keys**: Stored in Redis as hashes under `smartern_database_session:{session_id}`.
- **Cache Storage**: Stored as strings or serialized objects under `smartern_database_cache:{key_name}`.
- **Queue Buffering**: Background CSV import payloads are queued under list keys `smartern_database_queues:default` and processed asynchronously by Redis workers.

