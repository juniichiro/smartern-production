# Testing Suite (Pest PHP)

SmarTern uses **Pest PHP** for testing backend behavior, authentication flows, role access levels, and security constraints.

---

## Pest Version

SmarTern runs on **Pest v4**. Tests are defined under the `tests/` directory:
- `tests/Unit`: Low-level standalone validation and helper tests.
- `tests/Feature`: HTTP request assertions, controllers, middleware, and database transaction checks.

---

## Running Tests

To run the full test suite, use the composer script:

```bash
composer test
```

Or run Pest directly:

```bash
vendor/bin/pest
```

### Filtering Tests
To run a specific test file or filter by name:
```bash
vendor/bin/pest tests/Feature/AttendanceTest.php
vendor/bin/pest --filter="geofencing"
```

---

## Key Test Coverage

1. **Authentication Flows**: Validates password length checks (min 12 characters), lockout rules after 5 failed attempts, and TOTP verification.
2. **Role Authorization**: Verifies that Interns, Supervisors, Head Supervisors, School Coordinators, and Admins can only access their respective dashboard routes.
3. **GPS Geofencing**: Simulates clock-in actions with coordinates within and outside the configured radius to confirm clock-in is correctly blocked when out of bounds.
4. **Bulk CSV Import**: Verifies CSV file schema validation, row formatting, and background queue jobs creation.
