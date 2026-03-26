# Laravel Docker Boilerplate

Generic Docker boilerplate for **Laravel 12 + PHP 8.4** projects.  
Clone, rename your project, and you're running in one command — both dev and production.

---

## Stack

| Service    | Image                 | Port |
|------------|-----------------------|------|
| PHP-FPM    | `php:8.4-fpm-alpine`  | 9000 (internal) |
| Nginx      | `nginx:1-alpine`      | `APP_PORT` |
| PostgreSQL | `postgres:17-alpine`  | 5432 |
| Redis      | `redis:7-alpine`      | 6379 |
| Queue      | PHP 8.4 worker        | — |
| Scheduler  | PHP 8.4 cron          | — |
| Node/Vite  | `node:22-alpine`      | `VITE_PORT` (dev only) |
| Mailpit    | `axllent/mailpit`     | `MAILPIT_PORT` (dev only) |

---

## Quick Start

```bash
git clone <repo-url> my-project
cd my-project
./setup.sh
```

The script:
- Creates `.env` from `.env.example`, using `PROJECT_NAME` to derive DB credentials
- Auto-generates secure 32-char passwords for `POSTGRES_PASSWORD` and `REDIS_PASSWORD` if empty
- Syncs credentials into `src/.env`
- Builds and starts all containers
- Runs `composer install`, `key:generate`, and `migrate` automatically

**Production:**

```bash
./setup.sh --prod
```

Differences in `--prod` mode:
- Skips `node` and `mailpit` containers (no override)
- `composer install --no-dev --optimize-autoloader`
- Sets `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`, `SESSION_SECURE_COOKIE=true` in `src/.env`
- Runs `config:cache`, `route:cache`, `view:cache`
- Uses `php.production.ini` (OPcache without timestamp validation)

---

## Project Structure

```
├── docker-compose.yml              # Base services (production)
├── docker-compose.override.yml     # Dev services (node + mailpit) — auto-loaded
├── setup.sh                        # One-command setup (--prod for production)
├── rename.sh                       # Rename project (replaces myproject everywhere)
├── .env.example                    # Docker env template (passwords auto-generated)
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf          # Global Nginx config (prod CSP)
│   │   ├── nginx.dev.conf      # Global Nginx config (dev CSP with Vite)
│   │   └── default.conf        # Server block
│   ├── php/
│   │   ├── Dockerfile          # Multi-stage build (builder + production)
│   │   ├── php.local.ini       # PHP config for local dev
│   │   └── php.production.ini  # PHP config for production
│   ├── postgres/
│   │   └── init.sh             # PostgreSQL initialization (uuid-ossp, unaccent, pg_trgm)
│   └── redis/
│       └── redis.conf          # Redis configuration
└── src/                            # Laravel source code
    ├── app/
    │   └── Providers/          # AppServiceProvider (Laravel core)
    ├── config/
    ├── database/
    │   ├── migrations/         # Core migrations (cache, jobs)
    │   └── seeders/            # DatabaseSeeder (entry point)
    ├── modules/                # Modular architecture
    │   ├── Auth/
    │   ├── Notification/
    │   ├── Permission/
    │   └── User/
    ├── routes/
    └── ...
```

---

## Customising for a New Project

Run the rename script to replace `myproject` across all config templates:

```bash
./rename.sh yadah-productions
```

This updates `.env.example`, `src/.env.example`, and `README.md` — converting hyphens to underscores for DB names (`yadah_productions_db`, `yadah_productions_user`).

Then run setup:

```bash
./setup.sh
```

`setup.sh` automatically sets `POSTGRES_DB=${PROJECT_NAME}_db` and `POSTGRES_USER=${PROJECT_NAME}_user`.

For `src/.env`, configure:

```env
APP_NAME="My App"
APP_TIMEZONE=Europe/Lisbon  # or America/Sao_Paulo, UTC, etc.
```

That's it. No more manual find-and-replace across multiple files.

---

## Useful Commands

A `Makefile` wraps all common day-to-day operations. Run `make help` for the full list.

```bash
make setup          # First-time setup (dev)
make setup-prod     # First-time setup (production)

make up             # Start all containers
make down           # Stop containers (keep volumes)
make restart        # Restart all containers
make build          # Rebuild app image and restart

make ps             # Container status
make logs           # Follow app logs
make logs-nginx     # Follow nginx logs
make logs-queue     # Follow queue worker logs

make shell          # Open shell in app container
make artisan CMD="migrate:status"
make composer CMD="require vendor/pkg"
make npm CMD="install"

make migrate        # Run migrations
make migrate-fresh  # Drop all tables and re-run ⚠️
make tinker         # Open Laravel Tinker

make cache-clear    # Clear all caches
make cache-warm     # Cache config + routes + views (production)

make test           # Run all tests
make test-unit      # Unit tests only
make test-feature   # Feature tests only

make reset          # Destroy containers, volumes and .env files ⚠️
```

---

## Infrastructure Notes

### PHP (multi-stage Dockerfile)
- **Stage 1 — Builder**: compiles extensions (pdo_pgsql, redis, gd, zip, bcmath, pcntl, opcache)
- **Stage 2 — Production**: lean image with runtime only, no build tools
- **Stage 3 — Development**: extends production, adds bash and dev tooling
- Runs as non-root user (UID 1000)
- Two PHP config files:
  - `php.local.ini`: `display_errors=On`, `validate_timestamps=1`, `cookie_secure=Off`
  - `php.production.ini`: `display_errors=Off`, `validate_timestamps=0`, `cookie_secure=On`
  - Selected automatically via `APP_ENV` in the volume mount

### Nginx
- `server_tokens off`
- Security headers: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `CSP`, `Permissions-Policy`
- Rate limiting: API (`30r/s`), login (`5r/m`)
- Gzip compression for assets
- 30-day cache for static files
- Blocks access to hidden files (`.env`, `.git`) and sensitive extensions (`.sql`, `.log`, `.sh`)
- Allowed HTTP methods: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`

### PostgreSQL
- Pre-installed extensions: `uuid-ossp`, `unaccent`, `pg_trgm`
- Healthcheck with `pg_isready`

### Redis
- 3 separate databases: cache (db0), queues (db1), sessions (db2)
- Eviction policy: `allkeys-lru`
- AOF persistence with `appendfsync everysec`
- Password via environment variable

### Queue Worker
- Processes jobs via Redis with auto-retry (`--tries=3`)
- Max 1000 jobs or 1 hour per worker (`--max-jobs=1000 --max-time=3600`)

### Scheduler
- Runs `php artisan schedule:work`

### Node/Vite *(dev only)*
- Defined in `docker-compose.override.yml`, auto-loaded in dev
- Runs `npm install` only if `node_modules` is missing, then starts Vite with HMR
- For production: build assets with `npm run build` inside the container, and commit `public/build/`

### Mailpit *(dev only)*
- Catches all outgoing emails — nothing reaches the internet
- Web UI at `http://localhost:8025`
- SMTP on port `1025`, pre-configured in `src/.env`

### Dev vs Production

| File | Loaded | Contents |
|------|--------|----------|
| `docker-compose.yml` | Always | app, nginx, postgres, redis, queue, scheduler |
| `docker-compose.override.yml` | Dev (auto) | node (Vite), mailpit, dev nginx CSP |
| `nginx.conf` | Production | Strict CSP (`'self'`) |
| `nginx.dev.conf` | Dev (via override) | CSP with `http://localhost:5173` |
| `php.local.ini` | Dev | `display_errors=On`, timestamp revalidation |
| `php.production.ini` | Production | `display_errors=Off`, OPcache fully optimised |

### Resource Limits

| Service   | Env  | CPU max | Memory max |
|-----------|------|---------|------------|
| App       | base | 1.0     | 512M       |
| Nginx     | base | 0.5     | 128M       |
| PostgreSQL| base | 1.0     | 512M       |
| Redis     | base | 0.5     | 256M       |
| Queue     | base | 0.5     | 256M       |
| Scheduler | base | 0.5     | 256M       |
| Node      | dev  | 0.5     | 512M       |
| Mailpit   | dev  | 0.25    | 64M        |

