# Packages & Dependencies Reference

This document provides a detailed catalog of the backend Composer packages, frontend NPM modules, and Python libraries powering the SmarTern application.

---

## 1. Backend PHP Packages (`composer.json`)

These packages are installed via Composer and managed inside `vendor/`.

### Core Production Dependencies
- **`laravel/framework` (`^13.0`)**: Core MVC web framework.
- **`laravel/reverb` (`^1.0`)**: A high-performance, native PHP WebSocket server implementing real-time broadcasting.
- **`pragmarx/google2fa-laravel` (`^3.0`)**: Implements Time-Based One-Time Passwords (TOTP) for Multi-Factor Authentication.
- **`barryvdh/laravel-dompdf` (`^3.1`)**: A DOMPDF wrapper for Laravel used to compile and export formal completion certificates.
- **`maatwebsite/excel` (`^3.1`)**: Integrates PHP Spreadsheet to import and export rosters in bulk CSV formats.
- **`vinkla/hashids` (`^14.0`)**: Generates short, unique, non-sequential hashes from integer IDs to prevent ID harvesting in URL parameters.
- **`simplesoftwareio/simple-qrcode` (`^4.2`)**: Formulates secure QR codes used by interns to enroll in MFA.
- **`league/flysystem-aws-s3-v3` (`^3.0`)**: Integrates Amazon S3 filesystem drivers for cloud storage of resume PDFs and screenshots.
- **`ezyang/htmlpurifier`**: Secures inputs and accomplishment logs by purifying potential cross-site scripting (XSS) payloads.

### Development & Testing Dependencies
- **`pestphp/pest` (`^4.6`)** & **`pestphp/pest-plugin-laravel` (`^4.1`)**: The testing framework used to execute unit, integration, and browser testing.
- **`laravel/breeze`**: Provides scaffolding for auth views, routers, and controller pipelines.
- **`laravel/pint` (`^1.27`)**: An opinionated PHP code style linter to keep spacing, naming, and loops consistent.
- **`mockery/mockery` (`^1.6`)**: Mock framework used in integration tests to mock service boundaries.

---

## 2. Frontend JavaScript Packages (`package.json`)

These packages are compiled using Vite and managed inside `node_modules/`.

### Core Production Dependencies
- **`alpinejs` (`^3.4.2`)**: The reactive micro-framework managing state, dropdowns, and coordinates capture.
- **`@alpinejs/collapse` (`^3.15.12`)**: An Alpine.js plugin that provides smooth transitions for collapsing elements.
- **`chart.js` (`^4.5.1`)**: Direct canvas data rendering library for supervisor/admin dashboard metrics charts.
- **`laravel-echo` (`^2.3.4`)**: Registers channel interfaces to bind WebSocket client listeners to Reverb events.
- **`pusher-js` (`^8.5.0`)**: Connection adapter for Laravel Echo to communicate using Pusher protocol sockets.
- **`axios` (`^1.15.2`)**: Promise-based HTTP client managing all backend request flows.

### Development & Styling Dependencies
- **`tailwindcss` (`^3.4.17`)**: The primary CSS utility library.
- **`postcss` (`^8.5.15`)** & **`autoprefixer` (`^10.5.0`)**: Postprocessors compiling utilities and inserting browser vendors.
- **`vite` (`^6.0.0`)** & **`laravel-vite-plugin` (`^1.3.0`)**: Build pipeline compilation server.
- **`concurrently` (`^9.0.1`)**: Parallel script execution helper running Vite compilers and database queues simultaneously.

---

## 3. Python Modules (`requirements.txt`)

These modules run the machine learning isolation forest anomaly calculations and Rete pattern placements.

- **`scikit-learn`**: Machine learning framework instantiating the Isolation Forest estimator model.
- **`pandas`**: Data handling library packaging database audit logs into formatted feature arrays.
- **`numpy`**: Provides mathematical models and vector transformations.
- **`mysql-connector-python`**: Interface managing continuous MySQL database queries within background daemons.
