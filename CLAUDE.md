# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

All commands run through Docker via the Makefile. The app source lives in `src/`.

```bash
make setup          # First-time setup (copies .env, starts containers, runs migrations+seeds)
make up             # Start containers
make down           # Stop containers

make test           # Run all test suites
make test-unit      # Unit tests only
make test-feature   # Feature tests only

# Run a single test suite (defined in src/phpunit.xml)
make artisan CMD="test --testsuite=Auth-Api"
make artisan CMD="test --testsuite=User-Api"
make artisan CMD="test --testsuite=Permission-Api"
make artisan CMD="test --testsuite=Notification-Api"
make artisan CMD="test --testsuite=Export-Api"
make artisan CMD="test --testsuite=ActivityLog-Api"

# Run a single test file or method
make artisan CMD="test --filter=LoginTest"
make artisan CMD="test modules/Auth/Tests/Api/LoginTest.php"

make migrate        # Run migrations
make seed           # Run seeders
make cache-clear    # Clear all caches (config, routes, views, app)
make shell          # Open shell in app container
make artisan CMD="make:module Product"            # Scaffold a new module
make artisan CMD="make:module Product --with-views"  # Scaffold with Blade views
make artisan CMD="remove:module Product"          # Remove a module
```

Tests use SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) — no running database needed to run tests.

## Architecture

### Modular Structure

The application is organized into self-contained modules under `src/modules/`. Each module owns its full vertical slice:

```
src/modules/{Name}/
├── Database/Migrations/    # Module-specific migrations
├── Database/Seeders/
├── Database/Factories/
├── Http/Controllers/       # Dual-response controllers (JSON + Blade)
├── Http/Requests/          # Form Request validation
├── Http/Resources/         # API JSON transformation
├── Models/
├── Policies/               # Gate authorization
├── Providers/{Name}ServiceProvider.php   # Registers everything
├── Routes/api.php          # Prefixed at api/v1, middleware auth:api
├── Routes/web.php          # Session-based routes
├── Services/               # All business logic lives here
├── Events/ + Listeners/    # Domain events registered in ServiceProvider
├── Jobs/
└── Tests/Api/ + Tests/Web/
```

**Core modules:** Auth, User, Permission, Notification, Export, ActivityLog.

New modules are registered by adding their ServiceProvider to `src/bootstrap/providers.php`. The `make:module` command handles this automatically.

### Controller Pattern (Dual-Response)

Controllers respond to both API and web requests from a single method using `$request->expectsJson()`:

```php
public function index(Request $request): JsonResponse|\Illuminate\View\View
{
    Gate::authorize('viewAny', User::class);
    $users = $this->userService->getAll($perPage);

    if ($request->expectsJson()) {
        return UserResource::collection($users)->response();
    }
    return view('user::users.index', compact('users'));
}
```

Views use the module namespace (`auth::login`, `user::users.index`). API-only modules (Export, Notification, ActivityLog) omit the Blade branch.

### Service Layer

All business logic belongs in Services — controllers only validate, authorize, and format responses. Services dispatch events; they never call other services directly. Example: `UserService::create()` creates the model, assigns roles, and dispatches `UserCreated` — it does not know about emails or notifications.

### Event-Driven Cross-Module Communication

Modules communicate exclusively via events. Listeners in one module subscribe to events from another:

- `UserCreated` (Auth module) → `SendWelcomeEmail`, `LogUserCreation` (Auth), `NotifyOnUserCreated` (Notification)
- `UserDeleted` (User module) → `LogUserDeletion` (User), `NotifyOnUserDeleted` (Notification)

Each module's ServiceProvider registers its own listeners via `Event::listen()`. Cross-module listeners are registered in the subscribing module's ServiceProvider (e.g., `NotificationServiceProvider` listens to User and Permission events).

### Authorization

Uses `Gate::authorize()` with Spatie Permission strings (`user.create`, `role.delete`, `log.list`). Every controller method calls `Gate::authorize()` before touching the service. Policies translate permission strings to boolean checks.

### Authentication

- **Web:** Laravel session + Fortify (views in `modules/Auth/Resources/views`)
- **API:** Laravel Passport Bearer tokens (15-day access, 30-day refresh)
- **2FA:** TOTP via Fortify; login returns `{ two_factor: true, two_factor_token: '...' }` when enabled, then client POSTs to `/api/v1/auth/two-factor-challenge`

API routes use `middleware('api')` with `auth:api` guard. Web routes use `middleware('web')` with session.

### Async Export

`POST /api/v1/exports` queues `ProcessExportJob`. The job generates CSV/XLSX (Maatwebsite Excel) or PDF (Spatie Browsershot/Chromium), stores the file, updates `exports.status`, and sends an in-app notification. Poll `GET /api/v1/exports/{uuid}/status`, then download at `GET /api/v1/exports/{uuid}/download`.

New exportable modules implement `ExportableInterface` (see `modules/User/Services/UserExportService.php`).

### Adding a New Module

```bash
make artisan CMD="make:module {Name}"             # API-only
make artisan CMD="make:module {Name} --with-views"  # With Blade views
```

This generates the full directory structure, registers the ServiceProvider in `bootstrap/providers.php`, and adds test suites to `phpunit.xml`. Default seeders create `admin@example.com` and `user@example.com` (password: `password`) with admin and user roles respectively.
