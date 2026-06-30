# Installation & Setup

Set up the SmarTern development environment on your local machine.

## Prerequisites

Ensure you have the following installed on your system:
- **PHP**: `^8.3`
- **Composer**: Dependency manager for PHP
- **Node.js**: Version 18+ or 20+ (supports Vite)
- **Python**: 3.8+ (for machine learning features)
- **Database**: MySQL or SQLite

---

## 1. Clone the Repository

Clone the SmarTern repository and navigate into the project directory:

```bash
git clone https://github.com/juniichiro/SmarTern
cd SmarTern
```

---

## 2. Backend Dependencies

Install the PHP packages via Composer:

```bash
composer install
```

This installs core Laravel 13 framework dependencies along with:
- `pragmarx/google2fa-laravel` (for MFA functionality)
- `simplesoftwareio/simple-qrcode` (to generate login QR codes)
- `maatwebsite/excel` & `ezyang/htmlpurifier` (for bulk imports)
- `pestphp/pest` (for automated testing)

---

## 3. Frontend Dependencies

Install the Node.js packages:

```bash
npm install
```

This sets up Vite, Tailwind CSS v3, and Alpine.js for the responsive frontend layer.

---

## 4. Configure Environment

Copy the example environment file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` in your editor and configure your database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartern
DB_USERNAME=root
DB_PASSWORD=
```

---

## 5. Initialize Database

Run migrations and seed the database with initial configurations, offices, and administrative/mock roles:

```bash
php artisan migrate:fresh --seed
```

---

## 6. Machine Learning Environment

Install the required Python packages for the anomaly detection daemon:

```bash
pip install pandas scikit-learn numpy
```

---

## 7. Run the Application

Start the development stack (Web server, Vite builder, and Queue worker):

```bash
npm run dev
```

By default, this concurrently runs:
- `php artisan serve` on [http://127.0.0.1:8000](http://127.0.0.1:8000)
- `php artisan queue:listen` to process asynchronous notifications and CSV imports
- Vite dev server for live-reloading assets

To start or monitor the Python Isolation Forest daemon, run:
```bash
python scripts/if_daemon.py
```
*(Note: The `AnomalyDetectionService` will also attempt to auto-start this daemon when system actions occur).*
