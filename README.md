# Laravel Docker Production Stack

A containerized Laravel environment using Docker Compose with PHP 8.3-FPM, Caddy (HTTP/3 and Automatic HTTPS), MySQL 8.4, Queue Worker, and Cron Scheduler.

---

## 🏗️ Architecture Overview

The project uses Docker multi-stage builds ([Dockerfile](Dockerfile)) to separate duties across containers:

```
                      Client (Browser / Internet)
                                  │
                  Ports 80, 443 (TCP & UDP for HTTP/3)
                                  ▼
                     ┌─────────────────────────┐
                     │       laravel-web       │
                     │  (Caddy Reverse Proxy)  │
                     └────────────┬────────────┘
                                  │
                      FastCGI (port 9000)
                                  ▼
      ┌────────────────────────────────────────────────────────┐
      │                      laravel-app                       │
      │        (PHP 8.3-FPM Runtime & Laravel Application)     │
      └───────┬───────────────────────────────┬────────────────┘
              │                               │
              ▼                               ▼
   ┌──────────────────────┐        ┌──────────────────────┐
   │        queue         │        │      scheduler       │
   │ (php artisan queue)  │        │ (schedule:work cron) │
   └──────────────────────┘        └──────────────────────┘
              │
              ▼
   ┌──────────────────────┐
   │          db          │
   │     (MySQL 8.4)      │
   └──────────────────────┘
```

### Image Differences

| Service / Image | Base Image | Target | What Is Stored Inside | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| **`laravel-app`** (`app`, `queue`, `scheduler`) | `php:8.3-fpm-alpine` | `app` | **Full Laravel Application Code**, Composer packages (`vendor/`), PHP runtime & extensions (`pdo_mysql`, `redis`, etc.) | Executes PHP business logic, runs migrations on startup, processes queue jobs, runs cron schedules. |
| **`laravel-web`** (`web`) | `caddy:2-alpine` | `web` | **Static files only** (`/var/www/html/public`) and `Caddyfile` | Reverse proxy, automatic SSL termination, serves static assets (CSS, JS, images, uploaded media in `/storage`). Does **not** contain PHP code. |

---

## 🔒 Automatic HTTPS & Public Domain Setup

This project uses **Caddy**, which handles SSL/TLS certificates **automatically** via Let's Encrypt / ZeroSSL. You do **not** need Certbot or manual certificate renewals.

### Prerequisites
1. **Public DNS**: Point your domain's DNS **`A` record** to your server's public IP address (e.g. `app.yourdomain.com` $\rightarrow$ `203.0.113.10`).
2. **Firewall**: Ensure incoming traffic is allowed on:
   - Port `80` (HTTP - required for ACME domain challenge)
   - Port `443` (HTTPS - TCP and UDP for HTTP/3)

### How to Configure HTTPS

1. Open [`.env`](.env) in the root directory.
2. Set your public domain in `SITE_ADDRESS` and update `APP_URL`:

```dotenv
# Your public domain (Caddy will automatically request SSL for this)
SITE_ADDRESS=app.yourdomain.com

# Laravel Application URL with HTTPS
APP_URL=https://app.yourdomain.com

# Production settings
APP_ENV=production
APP_DEBUG=false
```

3. Restart the containers to apply:

```bash
docker compose up -d
```

### How Caddy Handles Certificates
- **Auto-Provisioning**: On startup, Caddy sees `SITE_ADDRESS` and contacts Let's Encrypt / ZeroSSL to verify domain ownership via HTTP-01 challenge on port 80.
- **Auto-Redirect**: All plain HTTP (`http://`) requests are automatically redirected to secure HTTPS (`https://`).
- **Persistence**: Certificates and private keys are saved inside the `caddy_data` Docker volume (`caddy_data:/data`), preventing rate limits when recreating containers.
- **Auto-Renewal**: Caddy automatically renews certificates in the background before they expire.

---

## 🗄️ Database & Automated Migrations

### Database Credentials (`.env`)
The database configuration is controlled by the root [`.env`](.env) file:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_ROOT_PASSWORD=rootpassword123
DB_DATABASE=product
DB_USERNAME=product
DB_PASSWORD=passwd123
```

> [!IMPORTANT]
> MySQL initializes its database and credentials **only on the first run** when the `db_data` volume is created. If you ever change the database name or passwords in `.env`, you must reset the volume (`docker compose down -v`).

### Automated Migrations
On container startup, `docker/entrypoint.sh` automatically runs:

```bash
php artisan migrate --force
```

All migration files are stored in `laravelweb1/database/migrations/`:
* `0001_01_01_000000_create_users_table.php`
* `0001_01_01_000001_create_cache_table.php`
* `0001_01_01_000002_create_jobs_table.php`
* `2026_09_21_000000_create_product_items_table.php` (Creates `product_items` table automatically)

---

## 🛠️ Common Operations & Commands

### Start All Services
```bash
docker compose up -d
```

### Rebuild Images and Start
```bash
docker compose up --build -d
```

### Stop All Services
```bash
docker compose down
```

### Reset Database & Volumes (Fresh Start)
```bash
docker compose down -v
docker compose up --build -d
```

### View Live Logs
```bash
# All logs
docker compose logs -f

# App container logs (PHP-FPM and migrations)
docker compose logs -f app

# Web server logs (Caddy & SSL)
docker compose logs -f web
```

### Execute Artisan Commands
```bash
docker compose exec app php artisan <command>

# Examples:
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app php artisan cache:clear
```
