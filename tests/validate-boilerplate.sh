#!/bin/bash
# ============================================
# Boilerplate Validation Test Suite
# ============================================
# Runs offline checks against the entire project
# structure, configs and scripts. No Docker needed.
#
# Usage: bash tests/validate-boilerplate.sh
# ============================================

set -e

PASS=0
FAIL=0
WARN=0

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

pass() { PASS=$((PASS + 1)); echo -e "  ${GREEN}✓${NC} $1"; }
fail() { FAIL=$((FAIL + 1)); echo -e "  ${RED}✗${NC} $1"; }
warn() { WARN=$((WARN + 1)); echo -e "  ${YELLOW}⚠${NC} $1"; }

# cd to project root (assuming script runs from tests/ or root)
cd "$(dirname "$0")/.."

echo ""
echo "============================================"
echo "  Boilerplate Validation Test Suite"
echo "============================================"
echo ""

# ==========================================
# 1. Required files exist
# ==========================================
echo "▸ Required files"

REQUIRED_FILES=(
    "docker-compose.yml"
    "docker-compose.override.yml"
    "Makefile"
    "setup.sh"
    "README.md"
    ".env.example"
    ".dockerignore"
    ".gitignore"
    "src/.env.example"
    "src/composer.json"
    "src/package.json"
    "src/package-lock.json"
    "src/phpunit.xml"
    "src/vite.config.js"
    "src/artisan"
    "src/bootstrap/app.php"
    "src/bootstrap/providers.php"
    "docker/php/Dockerfile"
    "docker/php/php.local.ini"
    "docker/php/php.production.ini"
    "docker/nginx/nginx.conf"
    "docker/nginx/nginx.dev.conf"
    "docker/nginx/default.conf"
    "docker/postgres/init.sh"
    "docker/redis/redis.conf"
)

for f in "${REQUIRED_FILES[@]}"; do
    if [ -f "$f" ]; then
        pass "$f"
    else
        fail "$f — missing"
    fi
done
echo ""

# ==========================================
# 2. setup.sh syntax + key sections
# ==========================================
echo "▸ setup.sh"

if bash -n setup.sh 2>/dev/null; then
    pass "syntax valid"
else
    fail "syntax error"
fi

# Check critical sections exist
for section in "CLEANUP_NEEDED" "sedi()" "find_free_port" "flock" "docker compose" "passport:keys" "migrate --force" "db:seed" "key:generate" "storage:link"; do
    if grep -q "$section" setup.sh; then
        pass "has '$section'"
    else
        fail "missing '$section'"
    fi
done

# Check error() does cleanup
if grep -A5 'error()' setup.sh | grep -q 'CLEANUP_NEEDED'; then
    pass "error() handles cleanup"
else
    fail "error() does not cleanup containers"
fi

# Check trap exists
if grep -q 'trap cleanup' setup.sh; then
    pass "trap cleanup defined"
else
    fail "trap cleanup missing"
fi

# Check portable sed
if grep -q 'sedi' setup.sh; then
    pass "uses portable sed (sedi)"
else
    warn "may not work on macOS (no sedi)"
fi

# Check prod asset extraction
if grep -q 'docker cp.*public/build' setup.sh; then
    pass "extracts Vite assets in production"
else
    fail "no Vite asset extraction for production"
fi

# Check prod seed safety
if grep -q 'Seed.*NÃO.*produção\|Seed.*not.*production' setup.sh; then
    pass "seed skipped in production"
else
    fail "seed runs in production (insecure)"
fi
echo ""

# ==========================================
# 3. Docker Compose
# ==========================================
echo "▸ docker-compose.yml"

# Required services
for svc in "app:" "nginx:" "postgres:" "redis:" "queue:" "scheduler:"; do
    if grep -q "^    ${svc}" docker-compose.yml; then
        pass "service $svc defined"
    else
        fail "service $svc missing"
    fi
done

# Healthchecks for every service
for svc in app nginx postgres redis queue scheduler; do
    if awk "/^    ${svc}:/{found=1;next} found && /^    [a-z]/{found=0} found" docker-compose.yml | grep -q "healthcheck"; then
        pass "$svc has healthcheck"
    else
        fail "$svc missing healthcheck"
    fi
done

# Redis has environment section
if awk '/^    redis:/{found=1;next} found && /^    [a-z]/{found=0} found' docker-compose.yml | grep -q "environment:"; then
    pass "redis has environment (REDIS_PASSWORD)"
else
    fail "redis missing environment (healthcheck will fail)"
fi

# start_period on postgres and redis
for svc in postgres redis; do
    if awk "/^    ${svc}:/{found=1;next} found && /^    [a-z]/{found=0} found" docker-compose.yml | grep -q "start_period"; then
        pass "$svc has start_period"
    else
        warn "$svc missing start_period"
    fi
done

# Ports bound to 127.0.0.1
EXPOSED_PORTS=$(grep -E '^\s+- "[0-9]' docker-compose.yml docker-compose.override.yml 2>/dev/null || true)
UNSAFE_PORTS=$(echo "$EXPOSED_PORTS" | grep -v '127.0.0.1' | grep -v '^$' || true)
if [ -z "$UNSAFE_PORTS" ]; then
    pass "all ports bound to 127.0.0.1"
else
    fail "ports exposed to 0.0.0.0: $UNSAFE_PORTS"
fi

# Resource limits exist
if grep -q "deploy:" docker-compose.yml && grep -q "limits:" docker-compose.yml; then
    pass "resource limits defined"
else
    warn "no resource limits"
fi

# Named volumes + network
if grep -q "postgres_data:" docker-compose.yml && grep -q "redis_data:" docker-compose.yml; then
    pass "named volumes defined"
else
    fail "named volumes missing"
fi

if grep -q "app_network:" docker-compose.yml; then
    pass "named network defined"
else
    fail "named network missing"
fi
echo ""

# ==========================================
# 4. docker-compose.override.yml
# ==========================================
echo "▸ docker-compose.override.yml"

for svc in "node:" "mailpit:"; do
    if grep -q "$svc" docker-compose.override.yml; then
        pass "dev service $svc defined"
    else
        fail "dev service $svc missing"
    fi
done

if grep -q "target: development" docker-compose.override.yml; then
    pass "app target overridden to development"
else
    fail "app target not overridden for dev"
fi

if grep -q "nginx.dev.conf" docker-compose.override.yml; then
    pass "nginx config overridden for dev"
else
    fail "nginx config not overridden for dev"
fi
echo ""

# ==========================================
# 5. Dockerfile
# ==========================================
echo "▸ Dockerfile"

DOCKERFILE="docker/php/Dockerfile"

# 4 stages
for stage in "AS builder" "AS node-builder" "AS production" "AS development"; do
    if grep -q "$stage" "$DOCKERFILE"; then
        pass "stage '$stage' exists"
    else
        fail "stage '$stage' missing"
    fi
done

# Key PHP extensions
for ext in pdo_pgsql pgsql zip gd bcmath pcntl opcache redis intl; do
    if grep -q "$ext" "$DOCKERFILE"; then
        pass "extension $ext installed"
    else
        fail "extension $ext missing"
    fi
done

# Non-root user
if grep -q "appuser" "$DOCKERFILE"; then
    pass "non-root user (appuser)"
else
    fail "running as root"
fi

# UID/GID configurable
if grep -q "ARG UID" "$DOCKERFILE" && grep -q "ARG GID" "$DOCKERFILE"; then
    pass "UID/GID configurable"
else
    warn "UID/GID not configurable"
fi

# Vite assets copied
if grep -q "COPY --from=node-builder" "$DOCKERFILE"; then
    pass "Vite assets copied from node-builder"
else
    fail "Vite assets not copied"
fi

# npm ci (deterministic)
if grep -q "npm ci" "$DOCKERFILE"; then
    pass "uses npm ci (deterministic)"
else
    warn "uses npm install instead of npm ci"
fi
echo ""

# ==========================================
# 6. .dockerignore
# ==========================================
echo "▸ .dockerignore"

# Must NOT exclude src/ entirely (node-builder needs it)
if grep -qE '^src/$' .dockerignore; then
    fail "excludes src/ entirely (breaks node-builder)"
else
    pass "src/ not fully excluded"
fi

# Should exclude heavy dirs
for pattern in ".git" "src/vendor" "src/node_modules"; do
    if grep -q "$pattern" .dockerignore; then
        pass "excludes $pattern"
    else
        warn "does not exclude $pattern"
    fi
done
echo ""

# ==========================================
# 7. Nginx configs
# ==========================================
echo "▸ Nginx configs"

# Production CSP — no unsafe-inline for scripts
PROD_CSP=$(grep "Content-Security-Policy" docker/nginx/nginx.conf || true)
SCRIPT_SRC=$(echo "$PROD_CSP" | grep -oE "script-src[^;]+" || true)
if echo "$SCRIPT_SRC" | grep -q "'self'" && ! echo "$SCRIPT_SRC" | grep -q "unsafe-inline"; then
    pass "production CSP: no unsafe-inline for scripts"
else
    fail "production CSP: unsafe-inline present for scripts"
fi

# Dev CSP — allows localhost (may be in a map variable, not inline)
if grep -q "localhost" docker/nginx/nginx.dev.conf; then
    pass "dev CSP: allows localhost (Vite)"
else
    fail "dev CSP: missing localhost for Vite"
fi

# Security headers in both configs
for conf in docker/nginx/nginx.conf docker/nginx/nginx.dev.conf; do
    for header in "X-Frame-Options" "X-Content-Type-Options" "Referrer-Policy" "Permissions-Policy"; do
        if grep -q "$header" "$conf"; then
            pass "$conf: $header present"
        else
            fail "$conf: $header missing"
        fi
    done
done

# default.conf — PHP-FPM fastcgi_pass
if grep -q "fastcgi_pass app:9000" docker/nginx/default.conf; then
    pass "default.conf: fastcgi_pass app:9000"
else
    fail "default.conf: wrong fastcgi_pass"
fi

# default.conf — hidden files blocked
if grep -Fq 'location ~ /\.' docker/nginx/default.conf; then
    pass "default.conf: hidden files blocked"
else
    fail "default.conf: hidden files not blocked"
fi

# default.conf — sensitive extensions blocked
if grep -q '\.env' docker/nginx/default.conf; then
    pass "default.conf: .env files blocked"
else
    fail "default.conf: .env files not blocked"
fi

# Assets have CSP
if grep -A15 'jpg|jpeg|png' docker/nginx/default.conf | grep -q "Content-Security-Policy"; then
    pass "default.conf: static assets have CSP"
else
    fail "default.conf: static assets missing CSP"
fi
echo ""

# ==========================================
# 8. Makefile
# ==========================================
echo "▸ Makefile"

EXPECTED_TARGETS=(
    "setup" "setup-prod" "up" "down" "restart" "build" "ps"
    "logs" "logs-nginx" "logs-queue"
    "shell" "artisan" "migrate" "migrate-fresh" "seed" "tinker" "composer" "npm"
    "cache-clear" "cache-warm"
    "db-dump" "db-restore"
    "test" "test-unit" "test-feature"
    "reset" "help"
)

for target in "${EXPECTED_TARGETS[@]}"; do
    if grep -qE "^${target}:" Makefile; then
        pass "target '$target' exists"
    else
        fail "target '$target' missing"
    fi
done

# .PHONY includes all targets (may span multiple lines with \)
PHONY_LINE=$(sed -n '/^\.PHONY/,/[^\\]$/p' Makefile | tr '\n' ' ')
MISSING_PHONY=""
for target in "${EXPECTED_TARGETS[@]}"; do
    if ! echo "$PHONY_LINE" | grep -qw "$target"; then
        MISSING_PHONY="$MISSING_PHONY $target"
    fi
done
if [ -z "$MISSING_PHONY" ]; then
    pass "all targets in .PHONY"
else
    warn "missing from .PHONY:$MISSING_PHONY"
fi
echo ""

# ==========================================
# 9. .env.example variables
# ==========================================
echo "▸ .env.example"

# Check all vars referenced in docker-compose.yml exist in .env.example
COMPOSE_VARS=$(grep -oE '\$\{[A-Z_]+' docker-compose.yml | sed 's/\${//' | sort -u)
for var in $COMPOSE_VARS; do
    # Skip vars with defaults (:-) and UID/GID (set by setup.sh)
    case "$var" in
        UID|GID|APP_ENV) continue ;;
    esac
    if grep -q "^${var}=" .env.example; then
        pass ".env.example has $var"
    else
        fail ".env.example missing $var"
    fi
done
echo ""

# ==========================================
# 10. Laravel config consistency
# ==========================================
echo "▸ Laravel configs"

# DB connection is pgsql
if grep -q 'DB_CONNECTION=pgsql' src/.env.example; then
    pass "DB_CONNECTION=pgsql"
else
    fail "DB_CONNECTION is not pgsql"
fi

# DB_HOST is postgres (docker service name)
if grep -q 'DB_HOST=postgres' src/.env.example; then
    pass "DB_HOST=postgres"
else
    fail "DB_HOST should be 'postgres'"
fi

# Redis host
if grep -q 'REDIS_HOST=redis' src/.env.example; then
    pass "REDIS_HOST=redis"
else
    fail "REDIS_HOST should be 'redis'"
fi

# Session driver is redis
if grep -q 'SESSION_DRIVER=redis' src/.env.example; then
    pass "SESSION_DRIVER=redis"
else
    warn "SESSION_DRIVER is not redis"
fi

# Queue driver is redis
if grep -q 'QUEUE_CONNECTION=redis' src/.env.example; then
    pass "QUEUE_CONNECTION=redis"
else
    warn "QUEUE_CONNECTION is not redis"
fi

# Cache store is redis
if grep -q 'CACHE_STORE=redis' src/.env.example; then
    pass "CACHE_STORE=redis"
else
    warn "CACHE_STORE is not redis"
fi
echo ""

# ==========================================
# 11. Module structure
# ==========================================
echo "▸ Module structure"

MODULES=("Auth" "User" "Permission" "Notification" "ActivityLog" "Export")
MODULE_DIRS=("Http/Controllers" "Routes" "Services" "Providers" "Tests")

for mod in "${MODULES[@]}"; do
    if [ -d "src/modules/$mod" ]; then
        pass "module $mod exists"
        for dir in "${MODULE_DIRS[@]}"; do
            if [ -d "src/modules/$mod/$dir" ]; then
                pass "  $mod/$dir"
            else
                warn "  $mod/$dir missing"
            fi
        done
    else
        fail "module $mod missing"
    fi
done

# Providers registered in bootstrap/providers.php
for mod in "${MODULES[@]}"; do
    if grep -q "Modules\\\\${mod}\\\\" src/bootstrap/providers.php; then
        pass "$mod registered in providers.php"
    else
        fail "$mod NOT registered in providers.php"
    fi
done
echo ""

# ==========================================
# 12. phpunit.xml
# ==========================================
echo "▸ phpunit.xml"

# Test suites for modules
for suite in "Auth-Web" "Auth-Api" "User-Api" "User-Web" "Permission-Api" "Permission-Web" "Notification-Api" "Notification-Web" "ActivityLog-Api" "Export-Api" "Unit" "Feature"; do
    if grep -q "name=\"$suite\"" src/phpunit.xml; then
        pass "test suite '$suite' defined"
    else
        fail "test suite '$suite' missing"
    fi
done

# Source includes modules
if grep -q '<directory>modules</directory>' src/phpunit.xml; then
    pass "coverage includes modules/"
else
    fail "coverage excludes modules/"
fi

# Source includes app
if grep -q '<directory>app</directory>' src/phpunit.xml; then
    pass "coverage includes app/"
else
    fail "coverage excludes app/"
fi
echo ""

# ==========================================
# 13. composer.json
# ==========================================
echo "▸ composer.json"

# Valid JSON
if python3 -c "import json; json.load(open('src/composer.json'))" 2>/dev/null; then
    pass "valid JSON"
else
    fail "invalid JSON"
fi

# Required packages
for pkg in "laravel/framework" "laravel/passport" "laravel/fortify" "spatie/laravel-permission" "spatie/laravel-activitylog" "maatwebsite/excel" "spatie/browsershot"; do
    if grep -q "\"$pkg\"" src/composer.json; then
        pass "requires $pkg"
    else
        fail "missing $pkg"
    fi
done

# Autoload includes Modules namespace
if grep -q '"Modules\\\\": "modules/"' src/composer.json; then
    pass "autoload: Modules\\\\ namespace"
else
    fail "autoload: Modules\\\\ namespace missing"
fi

# No SQLite in post-create-project-cmd
if grep -q "database.sqlite" src/composer.json; then
    warn "post-create-project-cmd still references SQLite"
else
    pass "no SQLite references"
fi
echo ""

# ==========================================
# 14. package.json
# ==========================================
echo "▸ package.json"

if python3 -c "import json; json.load(open('src/package.json'))" 2>/dev/null; then
    pass "valid JSON"
else
    fail "invalid JSON"
fi

if grep -q '"build"' src/package.json; then
    pass "has 'build' script (Vite)"
else
    fail "missing 'build' script (Dockerfile npm run build will fail)"
fi

if grep -q '"dev"' src/package.json; then
    pass "has 'dev' script (Vite HMR)"
else
    fail "missing 'dev' script"
fi
echo ""

# ==========================================
# 15. Redis config
# ==========================================
echo "▸ Redis config"

if grep -q 'protected-mode yes' docker/redis/redis.conf; then
    pass "protected-mode yes"
else
    warn "protected-mode not enabled"
fi

if grep -q 'appendonly yes' docker/redis/redis.conf; then
    pass "persistence enabled (AOF)"
else
    warn "no persistence"
fi

REDIS_DBS=$(grep -E '^databases' docker/redis/redis.conf | awk '{print $2}')
if [ "${REDIS_DBS:-0}" -ge 16 ]; then
    pass "databases $REDIS_DBS (sufficient)"
else
    warn "databases $REDIS_DBS (may be limiting)"
fi
echo ""

# ==========================================
# 16. postgres init.sh
# ==========================================
echo "▸ postgres init.sh"

if head -10 docker/postgres/init.sh | grep -q 'set -e'; then
    pass "set -e (fail-fast)"
else
    fail "missing set -e"
fi
echo ""

# ==========================================
# 17. Security checks
# ==========================================
echo "▸ Security"

# .gitignore covers sensitive files
for pattern in ".env" "vendor" "node_modules"; do
    if grep -rq "$pattern" .gitignore src/.gitignore 2>/dev/null; then
        pass ".gitignore covers $pattern"
    else
        fail ".gitignore missing $pattern"
    fi
done

# backups/ in gitignore
if grep -q "backups" .gitignore; then
    pass ".gitignore covers backups/"
else
    fail ".gitignore missing backups/"
fi

# No hardcoded passwords in committed files
HARDCODED=$(grep -rn 'PASSWORD=.\+' .env.example src/.env.example 2>/dev/null | grep -v 'PASSWORD=$' | grep -v 'PASSWORD=null' | grep -v 'PASSWORD=.*#' | grep -v 'REDIS_PASSWORD=\$' || true)
if [ -z "$HARDCODED" ]; then
    pass "no hardcoded passwords in .env.example files"
else
    fail "hardcoded passwords found: $HARDCODED"
fi
echo ""

# ==========================================
# 18. Multi-project coexistence
# ==========================================
echo "▸ Multi-project coexistence"

# Port conflict resolution
if grep -q 'is_port_in_use' setup.sh; then
    pass "has is_port_in_use() function"
else
    fail "missing port conflict detection"
fi

if grep -q 'find_free_port' setup.sh; then
    pass "has find_free_port() function"
else
    fail "missing automatic port resolution"
fi

# find_free_port tries up to 50 ports
if grep -q 'max_attempts=50' setup.sh; then
    pass "find_free_port has retry limit (50)"
else
    warn "find_free_port may not have retry limit"
fi

# Port reassignment updates .env
if grep -q 'sedi.*${VAR_NAME}=.*${NEW_PORT}' setup.sh; then
    pass "reassigned ports written back to .env"
else
    fail "reassigned ports not persisted to .env"
fi

# Ignores own project's ports (re-run safe)
if grep -q 'grep.*${project}-' setup.sh; then
    pass "is_port_in_use ignores own project containers"
else
    fail "is_port_in_use does not distinguish own project"
fi

# Concurrent execution lock (flock)
if grep -q 'flock -n' setup.sh; then
    pass "flock prevents concurrent setup executions"
else
    fail "no concurrency lock"
fi

# Project name derived from directory (unique per clone)
if grep -qE 'basename.*pwd\|PROJECT_NAME' setup.sh; then
    pass "project name derived from directory"
else
    warn "project name may not be unique per clone"
fi

# Docker compose uses PROJECT_NAME for container names
if grep -q 'name: \${PROJECT_NAME}' docker-compose.yml; then
    pass "compose project name uses \${PROJECT_NAME}"
else
    fail "compose project name not parameterized"
fi

# Container names use PROJECT_NAME prefix
CONTAINER_NAMES=$(grep 'container_name:' docker-compose.yml | grep -v '${PROJECT_NAME}' || true)
if [ -z "$CONTAINER_NAMES" ]; then
    pass "all container names use \${PROJECT_NAME} prefix"
else
    fail "hardcoded container names: $CONTAINER_NAMES"
fi

# Volumes use PROJECT_NAME (no collision between projects)
if grep -q '${PROJECT_NAME}' docker-compose.yml | head -1 >/dev/null 2>&1; then
    : # checked via container names
fi

# Passwords auto-generated (unique per project)
if grep -q 'urandom.*head.*32' setup.sh; then
    pass "passwords auto-generated from /dev/urandom (32 chars)"
else
    fail "passwords not auto-generated"
fi

# Orphan volume detection
if grep -q 'orphan\|ORPHAN\|volume.*rm' setup.sh; then
    pass "orphan volume detection on rename"
else
    warn "no orphan volume handling"
fi

# Ports to check includes all exposed services
for svc_port in "APP_PORT" "REDIS_PORT" "VITE_PORT" "MAILPIT_PORT" "MAILPIT_SMTP_PORT"; do
    if grep -q "$svc_port" setup.sh; then
        pass "$svc_port included in port resolution"
    else
        fail "$svc_port not checked for conflicts"
    fi
done

# Max port boundary check (65535)
if grep -q '65535' setup.sh; then
    pass "port upper boundary check (65535)"
else
    warn "no upper boundary check for ports"
fi
echo ""

# ==========================================
# 19. README cross-reference
# ==========================================
echo "▸ README consistency"

# Check documented make targets exist
README_TARGETS=$(grep -oE 'make [a-z_-]+' README.md | awk '{print $2}' | sort -u)
for target in $README_TARGETS; do
    if grep -qE "^${target}:" Makefile 2>/dev/null; then
        pass "README 'make $target' exists in Makefile"
    else
        fail "README documents 'make $target' but target missing"
    fi
done
echo ""

# ==========================================
# 20. config/app.php
# ==========================================
echo "▸ config/app.php"

for check in \
    "'env' => env.*'production':app.env defaults to production" \
    "'debug'.*false:app.debug defaults to false" \
    "'cipher' => 'AES-256-CBC':cipher is AES-256-CBC" \
    "'timezone'.*'UTC':timezone is UTC" \
    "'frontend_url':frontend_url key exists"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -qE "$pattern" src/config/app.php; then
        pass "$label"
    else
        fail "$label"
    fi
done
echo ""

# ==========================================
# 21. config/auth.php
# ==========================================
echo "▸ config/auth.php"

if grep -q "'driver' => 'passport'" src/config/auth.php; then
    pass "api guard driver is passport"
else
    fail "api guard driver is not passport"
fi

if grep -q 'Modules\\User\\Models\\User' src/config/auth.php; then
    pass "user model points to Modules\\User"
else
    fail "user model not Modules\\User\\Models\\User"
fi

if grep -q "'password_reset_tokens'" src/config/auth.php; then
    pass "password reset table defined"
else
    fail "password reset table missing"
fi
echo ""

# ==========================================
# 22. config/database.php
# ==========================================
echo "▸ config/database.php"

if grep -qE "'default' => env.*'pgsql'" src/config/database.php; then
    pass "default connection is pgsql"
else
    fail "default connection is not pgsql"
fi

if grep -qE "'client' => env.*'phpredis'" src/config/database.php; then
    pass "redis client is phpredis"
else
    fail "redis client is not phpredis"
fi

for db_check in "REDIS_QUEUE_DB.*1" "REDIS_SESSION_DB.*2"; do
    if grep -q "$db_check" src/config/database.php; then
        pass "redis DB separation: $db_check"
    else
        fail "redis DB separation missing: $db_check"
    fi
done

if grep -q "'session' =>" src/config/database.php; then
    pass "redis session connection defined"
else
    fail "redis session connection missing"
fi
echo ""

# ==========================================
# 23. config/cache.php
# ==========================================
echo "▸ config/cache.php"

if grep -qE "'default' => env.*'redis'" src/config/cache.php; then
    pass "default cache store is redis"
else
    fail "default cache store is not redis"
fi
echo ""

# ==========================================
# 24. config/fortify.php
# ==========================================
echo "▸ config/fortify.php"

# Fortify views = true because the project uses dual-response (Blade + JSON)
if grep -q "'views'" src/config/fortify.php; then
    pass "fortify views key present (dual-response Blade + JSON)"
else
    fail "fortify views key missing"
fi

if grep -q "'guard' => 'web'" src/config/fortify.php; then
    pass "guard is web"
else
    fail "guard is not web"
fi

if grep -q "'prefix' => 'auth'" src/config/fortify.php; then
    pass "prefix is 'auth'"
else
    warn "prefix is not 'auth'"
fi

if grep -q "'lowercase_usernames' => true" src/config/fortify.php; then
    pass "lowercase_usernames enabled"
else
    warn "lowercase_usernames not enabled"
fi

for feature in "Features::registration()" "Features::resetPasswords()" "Features::updateProfileInformation()" "Features::updatePasswords()"; do
    if grep -q "$feature" src/config/fortify.php; then
        pass "feature $feature enabled"
    else
        fail "feature $feature missing"
    fi
done

if grep -q "twoFactorAuthentication" src/config/fortify.php; then
    pass "2FA enabled"
else
    fail "2FA not enabled"
fi
echo ""

# ==========================================
# 25. config/passport.php
# ==========================================
echo "▸ config/passport.php"

if grep -q "'guard' => 'web'" src/config/passport.php; then
    pass "guard is web"
else
    fail "guard is not web"
fi

if grep -q "PASSPORT_PRIVATE_KEY" src/config/passport.php; then
    pass "private key loaded from env"
else
    fail "private key not from env"
fi

if grep -q "PASSPORT_PUBLIC_KEY" src/config/passport.php; then
    pass "public key loaded from env"
else
    fail "public key not from env"
fi
echo ""

# ==========================================
# 26. config/permission.php
# ==========================================
echo "▸ config/permission.php"

if grep -q 'Modules\\Permission\\Models\\Permission' src/config/permission.php; then
    pass "permission model is Modules\\Permission"
else
    fail "permission model not Modules\\Permission"
fi

if grep -q 'Modules\\Permission\\Models\\Role' src/config/permission.php; then
    pass "role model is Modules\\Permission"
else
    fail "role model not Modules\\Permission"
fi

if grep -q "'teams' => false" src/config/permission.php; then
    pass "teams disabled"
else
    warn "teams enabled (unexpected for this boilerplate)"
fi
echo ""

# ==========================================
# 27. config/queue.php
# ==========================================
echo "▸ config/queue.php"

if grep -qE "'default' => env.*'redis'" src/config/queue.php; then
    pass "default queue is redis"
else
    fail "default queue is not redis"
fi

if grep -qE "'connection' => env.*'queue'" src/config/queue.php; then
    pass "redis queue uses 'queue' connection"
else
    fail "redis queue not using 'queue' connection"
fi

if grep -qE "'driver' => env.*'database-uuids'" src/config/queue.php; then
    pass "failed job driver is database-uuids"
else
    warn "failed job driver is not database-uuids"
fi
echo ""

# ==========================================
# 28. config/session.php
# ==========================================
echo "▸ config/session.php"

if grep -qE "'encrypt' => env.*true" src/config/session.php; then
    pass "session encryption enabled"
else
    fail "session encryption not enabled"
fi

if grep -qE "'http_only' => env.*true" src/config/session.php; then
    pass "http_only cookies"
else
    fail "http_only not enabled"
fi

if grep -qE "'same_site' => env.*'lax'" src/config/session.php; then
    pass "same_site is lax"
else
    fail "same_site not lax"
fi

if grep -qE "'connection' => env.*'session'" src/config/session.php; then
    pass "session uses 'session' redis connection"
else
    fail "session not using separate redis connection"
fi
echo ""

# ==========================================
# 29. config/logging.php
# ==========================================
echo "▸ config/logging.php"

if grep -qE "'default' => env.*'stack'" src/config/logging.php; then
    pass "default channel is stack"
else
    fail "default channel is not stack"
fi

if grep -qE "'days' => env.*14" src/config/logging.php; then
    pass "log retention 14 days"
else
    warn "log retention not 14 days"
fi

if grep -q "'stderr'" src/config/logging.php; then
    pass "stderr channel exists (Docker-friendly)"
else
    warn "no stderr channel"
fi
echo ""

# ==========================================
# 30. config/mail.php
# ==========================================
echo "▸ config/mail.php"

if grep -qE "'default' => env.*'log'" src/config/mail.php; then
    pass "default mailer is log (safe for dev)"
else
    warn "default mailer is not log"
fi
echo ""

# ==========================================
# 31. config/filesystems.php
# ==========================================
echo "▸ config/filesystems.php"

if grep -qE "'default' => env.*'local'" src/config/filesystems.php; then
    pass "default disk is local"
else
    fail "default disk is not local"
fi
echo ""

# ==========================================
# 32. PHP INI — local vs production
# ==========================================
echo "▸ PHP INI settings"

# Production hardening
for check in \
    "display_errors = Off:prod display_errors Off" \
    "display_startup_errors = Off:prod display_startup_errors Off" \
    "opcache.validate_timestamps = 0:prod opcache no revalidation" \
    "session.cookie_secure = On:prod session cookie secure"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker/php/php.production.ini; then
        pass "$label"
    else
        fail "$label"
    fi
done

# Dev settings
for check in \
    "display_errors = On:local display_errors On" \
    "opcache.validate_timestamps = 1:local opcache revalidation" \
    "session.cookie_secure = Off:local cookie_secure Off (HTTP ok)"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker/php/php.local.ini; then
        pass "$label"
    else
        fail "$label"
    fi
done

# Both must have
for check in \
    "expose_php = Off:expose_php Off" \
    "allow_url_include = Off:allow_url_include Off" \
    "session.use_strict_mode = On:session strict mode On" \
    "upload_max_filesize = 10M:upload_max_filesize 10M" \
    "post_max_size = 12M:post_max_size 12M" \
    "memory_limit = 256M:memory_limit 256M"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    for ini in docker/php/php.local.ini docker/php/php.production.ini; do
        if grep -q "$pattern" "$ini"; then
            pass "$label ($(basename $ini))"
        else
            fail "$label ($(basename $ini))"
        fi
    done
done
echo ""

# ==========================================
# 33. bootstrap/app.php
# ==========================================
echo "▸ bootstrap/app.php"

if grep -q "health: '/up'" src/bootstrap/app.php; then
    pass "health check endpoint /up"
else
    fail "health check endpoint missing"
fi

if grep -q "routes/web.php" src/bootstrap/app.php; then
    pass "web routes loaded"
else
    fail "web routes not loaded"
fi

if grep -q "routes/console.php" src/bootstrap/app.php; then
    pass "console routes loaded"
else
    fail "console routes not loaded"
fi
echo ""

# ==========================================
# 34. Event registration (module ServiceProviders)
# ==========================================
echo "▸ Event registration (module ServiceProviders)"

# Events are registered in each module's ServiceProvider, not a central EventServiceProvider
ALL_PROVIDERS=$(cat src/modules/*/Providers/*.php 2>/dev/null)

for event in "Registered" "UserCreated" "UserUpdated" "UserDeleted" "RoleAssigned" "PermissionCreated" "PermissionUpdated" "PermissionDeleted" "NotificationRead" "NotificationDeleted"; do
    if echo "$ALL_PROVIDERS" | grep -q "${event}::class"; then
        pass "event $event registered"
    else
        fail "event $event not registered in any module ServiceProvider"
    fi
done

EVENT_COUNT=$(echo "$ALL_PROVIDERS" | grep -c 'Event::listen' || echo 0)
if [ "$EVENT_COUNT" -ge 10 ]; then
    pass "total Event::listen calls: $EVENT_COUNT (≥10)"
else
    fail "only $EVENT_COUNT Event::listen calls across module providers (expected ≥10)"
fi
echo ""

# ==========================================
# 35. Vite config details
# ==========================================
echo "▸ vite.config.js"

for check in \
    "resources/css/app.css:input includes css/app.css" \
    "resources/js/app.js:input includes js/app.js" \
    "tailwindcss:tailwindcss plugin" \
    "'0.0.0.0':server host 0.0.0.0" \
    "host: 'localhost':HMR host localhost" \
    "refresh: true:refresh enabled"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" src/vite.config.js; then
        pass "$label"
    else
        fail "$label"
    fi
done
echo ""

# ==========================================
# 36. TestCase.php
# ==========================================
echo "▸ TestCase"

if grep -q "RefreshDatabase" src/tests/TestCase.php; then
    pass "uses RefreshDatabase trait"
else
    fail "missing RefreshDatabase trait"
fi

if grep -q 'Illuminate\\Foundation\\Testing\\TestCase' src/tests/TestCase.php; then
    pass "extends Illuminate TestCase"
else
    fail "does not extend Illuminate TestCase"
fi
echo ""

# ==========================================
# 37. phpunit.xml — test environment
# ==========================================
echo "▸ phpunit.xml test env"

for check in \
    'name="APP_ENV" value="testing":APP_ENV=testing' \
    'name="BCRYPT_ROUNDS" value="4":BCRYPT_ROUNDS=4 (fast)' \
    'value=":memory:":DB in-memory' \
    'name="QUEUE_CONNECTION" value="sync":QUEUE_CONNECTION=sync' \
    'name="SESSION_DRIVER" value="array":SESSION_DRIVER=array' \
    'name="CACHE_STORE" value="array":CACHE_STORE=array' \
    'name="MAIL_MAILER" value="array":MAIL_MAILER=array'; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" src/phpunit.xml; then
        pass "$label"
    else
        fail "$label"
    fi
done
echo ""

# ==========================================
# 38. DatabaseSeeder
# ==========================================
echo "▸ DatabaseSeeder"

SEEDER="src/database/seeders/DatabaseSeeder.php"

for check in \
    "PermissionSeeder:calls PermissionSeeder" \
    "RoleSeeder:calls RoleSeeder" \
    "UserSeeder:calls UserSeeder" \
    "WithoutModelEvents:uses WithoutModelEvents"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" "$SEEDER"; then
        pass "$label"
    else
        fail "$label"
    fi
done

# UserSeeder creates admin and user (delegation pattern)
USER_SEEDER="src/modules/User/Database/Seeders/UserSeeder.php"
if [ -f "$USER_SEEDER" ]; then
    pass "UserSeeder.php exists"
    if grep -q "syncRoles.*admin\|assignRole.*admin" "$USER_SEEDER"; then
        pass "creates admin user"
    else
        fail "admin user not created in UserSeeder"
    fi
    if grep -q "syncRoles.*user\|assignRole.*user" "$USER_SEEDER"; then
        pass "creates regular user"
    else
        fail "regular user not created in UserSeeder"
    fi
    if grep -q 'Modules\\User\\Models\\User' "$USER_SEEDER"; then
        pass "uses Modules User model"
    else
        fail "UserSeeder not using Modules\\User\\Models\\User"
    fi
else
    fail "UserSeeder.php missing"
fi
echo ""

# ==========================================
# 39. Nginx — rate limiting & gzip
# ==========================================
echo "▸ Nginx rate limiting & gzip"

# Rate limiting zones
for check in \
    "zone=api:10m rate=30r/s:API rate limit 30r/s" \
    "zone=login:10m rate=5r/m:Login rate limit 5r/m"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker/nginx/nginx.conf; then
        pass "$label (nginx.conf)"
    else
        fail "$label (nginx.conf)"
    fi
done

# Rate limit application
if grep -q "zone=login burst=5" docker/nginx/default.conf; then
    pass "login rate limit applied in default.conf"
else
    fail "login rate limit not applied"
fi

if grep -q "zone=api burst=20" docker/nginx/default.conf; then
    pass "API rate limit applied in default.conf"
else
    fail "API rate limit not applied"
fi

if grep -q "limit_req_status 429" docker/nginx/default.conf; then
    pass "rate limit returns 429"
else
    fail "rate limit not returning 429"
fi

# Gzip
for check in \
    "gzip on:gzip enabled" \
    "gzip_comp_level 6:gzip level 6" \
    "gzip_min_length 256:gzip min 256 bytes"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker/nginx/nginx.conf; then
        pass "$label"
    else
        fail "$label"
    fi
done

if grep -q "application/json" docker/nginx/nginx.conf; then
    pass "gzip includes application/json"
else
    fail "gzip missing application/json"
fi

# Other nginx hardening
for check in \
    "server_tokens off:server_tokens off" \
    "client_max_body_size 12M:client_max_body_size 12M" \
    "worker_connections 1024:worker_connections 1024"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker/nginx/nginx.conf; then
        pass "$label"
    else
        fail "$label"
    fi
done

# default.conf specifics
for check in \
    "fastcgi_hide_header X-Powered-By:hides X-Powered-By" \
    "fastcgi_connect_timeout 60s:fastcgi timeout 60s" \
    "expires 30d:static assets cached 30d" \
    'GET|POST|PUT|PATCH|DELETE|OPTIONS:HTTP methods restricted'; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker/nginx/default.conf; then
        pass "$label"
    else
        fail "$label"
    fi
done
echo ""

# ==========================================
# 40. Docker Compose — detailed checks
# ==========================================
echo "▸ Docker Compose details"

# Network driver
if grep -q "driver: bridge" docker-compose.yml; then
    pass "network driver is bridge"
else
    fail "network driver is not bridge"
fi

# restart policy on all services
RESTART_COUNT=$(grep -c "restart: unless-stopped" docker-compose.yml)
if [ "$RESTART_COUNT" -ge 6 ]; then
    pass "all $RESTART_COUNT services have restart: unless-stopped"
else
    fail "only $RESTART_COUNT services have restart (expected ≥6)"
fi

# PHP ini mount uses APP_ENV
if grep -q 'php.${APP_ENV:-local}.ini' docker-compose.yml; then
    pass "PHP ini mount uses \${APP_ENV} variable"
else
    fail "PHP ini mount not dynamic"
fi

# Queue worker params
for check in \
    "queue:work redis:queue works redis" \
    "max-time=3600:queue max-time 1h" \
    "max-jobs=1000:queue max-jobs 1000" \
    "schedule:work:scheduler uses schedule:work"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker-compose.yml; then
        pass "$label"
    else
        fail "$label"
    fi
done

# Logging everywhere
LOG_COUNT=$(grep -c 'max-size.*10m' docker-compose.yml)
if [ "$LOG_COUNT" -ge 6 ]; then
    pass "logging configured on $LOG_COUNT services"
else
    fail "logging only on $LOG_COUNT services (expected ≥6)"
fi

# Image versions
for check in \
    "postgres:17-alpine:PostgreSQL 17-alpine" \
    "redis:7-alpine:Redis 7-alpine"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker-compose.yml; then
        pass "$label"
    else
        fail "$label"
    fi
done

# Redis requirepass
if grep -q "requirepass" docker-compose.yml; then
    pass "Redis requires password"
else
    fail "Redis has no password"
fi

# DNS
if grep -q "8.8.8.8" docker-compose.yml && grep -q "1.1.1.1" docker-compose.yml; then
    pass "DNS configured (8.8.8.8 + 1.1.1.1)"
else
    warn "custom DNS not configured"
fi

# depends_on service_healthy
HEALTHY_COUNT=$(grep -c "service_healthy" docker-compose.yml)
if [ "$HEALTHY_COUNT" -ge 4 ]; then
    pass "service_healthy deps: $HEALTHY_COUNT"
else
    fail "insufficient service_healthy dependencies"
fi

# Postgres tuning
if grep -q "shared_buffers=128MB" docker-compose.yml; then
    pass "postgres tuning params present"
else
    warn "postgres tuning not configured"
fi
echo ""

# ==========================================
# 41. Docker Compose Override details
# ==========================================
echo "▸ Docker Compose Override details"

if grep -q "node:22-alpine" docker-compose.override.yml; then
    pass "Node 22-alpine"
else
    fail "Node version not 22-alpine"
fi

if grep -q "5173" docker-compose.override.yml; then
    pass "Vite port 5173 exposed"
else
    fail "Vite port not exposed"
fi

if grep -q "npm install" docker-compose.override.yml; then
    pass "npm install fallback if node_modules missing"
else
    warn "no npm install fallback"
fi

if grep -q "@vite/client" docker-compose.override.yml; then
    pass "node healthcheck tests @vite/client"
else
    fail "node healthcheck missing"
fi

if grep -q "1025" docker-compose.override.yml; then
    pass "Mailpit SMTP port 1025"
else
    fail "Mailpit SMTP port missing"
fi

if grep -q "8025" docker-compose.override.yml; then
    pass "Mailpit Web UI port 8025"
else
    fail "Mailpit Web UI port missing"
fi
echo ""

# ==========================================
# 42. Dockerfile details
# ==========================================
echo "▸ Dockerfile details"

if grep -q "php:8.4-fpm-alpine" "$DOCKERFILE"; then
    pass "PHP 8.4-fpm-alpine base"
else
    fail "PHP base is not 8.4-fpm-alpine"
fi

if grep -q "composer:2" "$DOCKERFILE"; then
    pass "Composer 2.x"
else
    fail "Composer version not 2.x"
fi

if grep -q "node:22-alpine" "$DOCKERFILE"; then
    pass "Node 22-alpine in builder"
else
    fail "Node version not 22-alpine"
fi

if grep -q "EXPOSE 9000" "$DOCKERFILE"; then
    pass "exposes port 9000"
else
    fail "port 9000 not exposed"
fi

if grep -q "bash" "$DOCKERFILE"; then
    pass "development stage installs bash"
else
    fail "development stage missing bash"
fi

if grep -q "chromium" "$DOCKERFILE"; then
    pass "development stage installs Chromium (Browsershot)"
else
    fail "development stage missing Chromium (required for PDF export)"
fi

if grep -q "PUPPETEER_EXECUTABLE_PATH" "$DOCKERFILE"; then
    pass "PUPPETEER_EXECUTABLE_PATH configured"
else
    fail "PUPPETEER_EXECUTABLE_PATH not set"
fi

if grep -q "mkdir.*storage" "$DOCKERFILE"; then
    pass "storage directories created"
else
    fail "storage directories not created"
fi
echo ""

# ==========================================
# 43. Postgres init.sh extensions
# ==========================================
echo "▸ Postgres extensions"

for ext in "uuid-ossp" "unaccent" "pg_trgm"; do
    if grep -q "$ext" docker/postgres/init.sh; then
        pass "extension $ext"
    else
        fail "extension $ext missing"
    fi
done

if grep -q "IF NOT EXISTS" docker/postgres/init.sh; then
    pass "CREATE EXTENSION is idempotent"
else
    fail "not idempotent (no IF NOT EXISTS)"
fi
echo ""

# ==========================================
# 44. Redis config details
# ==========================================
echo "▸ Redis config details"

for check in \
    "maxmemory 256mb:maxmemory 256mb" \
    "maxmemory-policy allkeys-lru:eviction policy allkeys-lru" \
    "timeout 300:client timeout 300s" \
    "tcp-keepalive 60:tcp-keepalive 60s" \
    "lazyfree-lazy-eviction yes:lazy eviction" \
    "lazyfree-lazy-expire yes:lazy expire" \
    "appendfsync everysec:appendfsync everysec"; do
    pattern="${check%%:*}"
    label="${check##*:}"
    if grep -q "$pattern" docker/redis/redis.conf; then
        pass "$label"
    else
        fail "$label"
    fi
done
echo ""

# ==========================================
# 45. Module files (granular)
# ==========================================
echo "▸ Module files (Auth)"

AUTH_FILES=(
    "Http/Controllers/AuthController.php"
    "Services/AuthService.php"
    "Providers/AuthServiceProvider.php"
    "Providers/FortifyServiceProvider.php"
    "Routes/api.php"
    "Routes/web.php"
    "Actions/CreateNewUser.php"
    "Actions/ResetUserPassword.php"
    "Actions/UpdateUserPassword.php"
    "Actions/UpdateUserProfileInformation.php"
    "Http/Requests/LoginRequest.php"
    "Http/Requests/RegisterRequest.php"
    "Http/Resources/AuthResource.php"
)
for f in "${AUTH_FILES[@]}"; do
    if [ -f "src/modules/Auth/$f" ]; then
        pass "Auth/$f"
    else
        fail "Auth/$f missing"
    fi
done
echo ""

echo "▸ Module files (User)"

USER_FILES=(
    "Models/User.php"
    "Http/Controllers/UserController.php"
    "Services/UserService.php"
    "Policies/UserPolicy.php"
    "Database/Factories/UserFactory.php"
    "Http/Requests/StoreUserRequest.php"
    "Http/Requests/UpdateUserRequest.php"
    "Http/Resources/UserResource.php"
    "Providers/UserServiceProvider.php"
    "Routes/api.php"
    "Routes/web.php"
    "Events/UserUpdated.php"
    "Events/UserDeleted.php"
    "Listeners/LogUserUpdate.php"
    "Listeners/LogUserDeletion.php"
)
for f in "${USER_FILES[@]}"; do
    if [ -f "src/modules/User/$f" ]; then
        pass "User/$f"
    else
        fail "User/$f missing"
    fi
done
echo ""

echo "▸ Module files (Permission)"

PERM_FILES=(
    "Models/Permission.php"
    "Models/Role.php"
    "Http/Controllers/PermissionController.php"
    "Http/Controllers/RoleController.php"
    "Services/PermissionService.php"
    "Services/RoleService.php"
    "Policies/PermissionPolicy.php"
    "Policies/RolePolicy.php"
    "Database/Seeders/PermissionSeeder.php"
    "Database/Seeders/RoleSeeder.php"
    "Http/Resources/PermissionResource.php"
    "Http/Resources/RoleResource.php"
    "Providers/PermissionServiceProvider.php"
    "Routes/api.php"
    "Routes/web.php"
)
for f in "${PERM_FILES[@]}"; do
    if [ -f "src/modules/Permission/$f" ]; then
        pass "Permission/$f"
    else
        fail "Permission/$f missing"
    fi
done
echo ""

echo "▸ Module files (Notification)"

NOTIF_FILES=(
    "Models/Notification.php"
    "Http/Controllers/NotificationController.php"
    "Services/NotificationService.php"
    "Policies/NotificationPolicy.php"
    "Http/Resources/NotificationResource.php"
    "Providers/NotificationServiceProvider.php"
    "Routes/api.php"
    "Routes/web.php"
    "Events/NotificationRead.php"
    "Events/NotificationDeleted.php"
    "Listeners/LogNotificationRead.php"
    "Listeners/LogNotificationDeletion.php"
)
for f in "${NOTIF_FILES[@]}"; do
    if [ -f "src/modules/Notification/$f" ]; then
        pass "Notification/$f"
    else
        fail "Notification/$f missing"
    fi
done
echo ""

echo "▸ Module files (ActivityLog)"

ACTLOG_FILES=(
    "Http/Controllers/ActivityLogController.php"
    "Http/Resources/ActivityLogResource.php"
    "Services/ActivityLogService.php"
    "Policies/ActivityLogPolicy.php"
    "Providers/ActivityLogServiceProvider.php"
    "Routes/api.php"
)
for f in "${ACTLOG_FILES[@]}"; do
    if [ -f "src/modules/ActivityLog/$f" ]; then
        pass "ActivityLog/$f"
    else
        fail "ActivityLog/$f missing"
    fi
done
echo ""

echo "▸ Module files (Export)"

EXPORT_FILES=(
    "Contracts/ExportableInterface.php"
    "Models/Export.php"
    "Services/ExportService.php"
    "Jobs/ProcessExportJob.php"
    "Commands/PurgeExpiredExportsCommand.php"
    "Notifications/ExportReadyNotification.php"
    "Http/Controllers/ExportController.php"
    "Http/Requests/ExportRequest.php"
    "Providers/ExportServiceProvider.php"
    "Routes/api.php"
    "Database/Migrations/2026_04_22_202724_create_exports_table.php"
)
for f in "${EXPORT_FILES[@]}"; do
    if [ -f "src/modules/Export/$f" ]; then
        pass "Export/$f"
    else
        fail "Export/$f missing"
    fi
done

# User and ActivityLog export services
for f in \
    "src/modules/User/Exports/UsersExport.php" \
    "src/modules/User/Services/UserExportService.php" \
    "src/modules/User/Resources/views/exports/pdf.blade.php" \
    "src/modules/ActivityLog/Exports/ActivityLogExport.php" \
    "src/modules/ActivityLog/Services/ActivityLogExportService.php" \
    "src/modules/ActivityLog/Resources/views/exports/pdf.blade.php"; do
    if [ -f "$f" ]; then
        pass "$(echo $f | sed 's|src/modules/||')"
    else
        fail "$(echo $f | sed 's|src/modules/||') missing"
    fi
done

# Export config
if [ -f "src/config/export.php" ]; then
    pass "config/export.php exists"
    if grep -q "sync_limit" src/config/export.php; then
        pass "export config: sync_limit defined"
    else
        fail "export config: sync_limit missing"
    fi
    if grep -q "expiration_hours" src/config/export.php; then
        pass "export config: expiration_hours defined"
    else
        fail "export config: expiration_hours missing"
    fi
else
    fail "config/export.php missing"
fi

# Export env vars
for var in "EXPORT_SYNC_LIMIT" "EXPORT_EXPIRATION_HOURS"; do
    if grep -q "^${var}=" src/.env.example; then
        pass "src/.env.example has $var"
    else
        fail "src/.env.example missing $var"
    fi
done
echo ""

# ==========================================
# 46. Migrations
# ==========================================
echo "▸ Migrations"

# Base migrations
if [ -f "src/database/migrations/0001_01_01_000001_create_cache_table.php" ]; then
    pass "base: create_cache_table"
else
    fail "base: create_cache_table missing"
fi

if [ -f "src/database/migrations/0001_01_01_000002_create_jobs_table.php" ]; then
    pass "base: create_jobs_table"
else
    fail "base: create_jobs_table missing"
fi

# Module migrations
USER_MIG=$(find src/modules/User/Database/Migrations -name '*create_users_table*' 2>/dev/null | head -1)
if [ -n "$USER_MIG" ]; then
    pass "User: create_users_table migration"
else
    fail "User: create_users_table migration missing"
fi

AUTH_MIG_COUNT=$(find src/modules/Auth/Database/Migrations -name '*.php' 2>/dev/null | wc -l)
if [ "$AUTH_MIG_COUNT" -ge 6 ]; then
    pass "Auth: $AUTH_MIG_COUNT OAuth migrations (≥6)"
else
    fail "Auth: only $AUTH_MIG_COUNT migrations (expected ≥6)"
fi

PERM_MIG=$(find src/modules/Permission/Database/Migrations -name '*permission_tables*' 2>/dev/null | head -1)
if [ -n "$PERM_MIG" ]; then
    pass "Permission: permission_tables migration"
else
    fail "Permission: permission_tables migration missing"
fi

NOTIF_MIG=$(find src/modules/Notification/Database/Migrations -name '*notifications_table*' 2>/dev/null | head -1)
if [ -n "$NOTIF_MIG" ]; then
    pass "Notification: notifications_table migration"
else
    fail "Notification: notifications_table migration missing"
fi

ACTLOG_MIG=$(find src/database/migrations -name '*activity_log*' 2>/dev/null | head -1)
if [ -n "$ACTLOG_MIG" ]; then
    pass "ActivityLog: activity_log migration"
else
    fail "ActivityLog: activity_log migration missing"
fi

EXPORT_MIG=$(find src/modules/Export/Database/Migrations -name '*exports_table*' 2>/dev/null | head -1)
if [ -n "$EXPORT_MIG" ]; then
    pass "Export: create_exports_table migration"
else
    fail "Export: create_exports_table migration missing"
fi

# ActivityLog config
if [ -f "src/config/activitylog.php" ]; then
    pass "config/activitylog.php exists"
    if grep -q "include_soft_deleted_subjects" src/config/activitylog.php; then
        pass "activitylog config: include_soft_deleted_subjects"
    else
        fail "activitylog config: include_soft_deleted_subjects missing"
    fi
else
    fail "config/activitylog.php missing"
fi

# Migration schema checks
if grep -q "Schema::create('cache'" src/database/migrations/0001_01_01_000001_create_cache_table.php; then
    pass "cache migration creates 'cache' table"
else
    fail "cache migration schema wrong"
fi

if grep -q "Schema::create('jobs'" src/database/migrations/0001_01_01_000002_create_jobs_table.php; then
    pass "jobs migration creates 'jobs' table"
else
    fail "jobs migration schema wrong"
fi
echo ""

# ==========================================
# 47. Module test files
# ==========================================
echo "▸ Module test files"

AUTH_TESTS=$(find src/modules/Auth/Tests -name '*.php' 2>/dev/null | wc -l)
if [ "$AUTH_TESTS" -ge 5 ]; then
    pass "Auth: $AUTH_TESTS test files (≥5)"
else
    fail "Auth: only $AUTH_TESTS test files (expected ≥5)"
fi

USER_TESTS=$(find src/modules/User/Tests -name '*.php' 2>/dev/null | wc -l)
if [ "$USER_TESTS" -ge 1 ]; then
    pass "User: $USER_TESTS test files"
else
    fail "User: no test files"
fi

PERM_TESTS=$(find src/modules/Permission/Tests -name '*.php' 2>/dev/null | wc -l)
if [ "$PERM_TESTS" -ge 1 ]; then
    pass "Permission: $PERM_TESTS test files"
else
    fail "Permission: no test files"
fi

NOTIF_TESTS=$(find src/modules/Notification/Tests -name '*.php' 2>/dev/null | wc -l)
if [ "$NOTIF_TESTS" -ge 1 ]; then
    pass "Notification: $NOTIF_TESTS test files"
else
    fail "Notification: no test files"
fi

ACTIVITY_TESTS=$(find src/modules/ActivityLog/Tests -name '*.php' 2>/dev/null | wc -l)
if [ "$ACTIVITY_TESTS" -ge 1 ]; then
    pass "ActivityLog: $ACTIVITY_TESTS test files"
else
    fail "ActivityLog: no test files"
fi

EXPORT_TESTS=$(find src/modules/Export/Tests -name '*.php' 2>/dev/null | wc -l)
if [ "$EXPORT_TESTS" -ge 1 ]; then
    pass "Export: $EXPORT_TESTS test files"
else
    fail "Export: no test files"
fi

EVENT_TESTS=$(find src/tests/Feature -name '*Event*' 2>/dev/null | wc -l)
if [ "$EVENT_TESTS" -ge 1 ]; then
    pass "Feature: $EVENT_TESTS event test files"
else
    warn "Feature: no event test files"
fi
echo ""

# ==========================================
# 48. Public directory
# ==========================================
echo "▸ Public directory"

for f in "index.php" "robots.txt"; do
    if [ -f "src/public/$f" ]; then
        pass "public/$f exists"
    else
        fail "public/$f missing"
    fi
done
echo ""

# ==========================================
# 49. CSS/JS entry points
# ==========================================
echo "▸ CSS/JS entry points"

if [ -f "src/resources/css/app.css" ]; then
    pass "resources/css/app.css exists"
    if grep -q "tailwindcss" src/resources/css/app.css; then
        pass "app.css imports tailwindcss"
    else
        fail "app.css does not import tailwindcss"
    fi
else
    fail "resources/css/app.css missing"
fi

if [ -f "src/resources/js/app.js" ]; then
    pass "resources/js/app.js exists"
else
    fail "resources/js/app.js missing"
fi

if [ -f "src/resources/js/bootstrap.js" ]; then
    pass "resources/js/bootstrap.js exists"
    if grep -q "axios" src/resources/js/bootstrap.js; then
        pass "bootstrap.js configures axios"
    else
        warn "bootstrap.js does not configure axios"
    fi
else
    warn "resources/js/bootstrap.js missing"
fi
echo ""

# ==========================================
# 50. Routes
# ==========================================
echo "▸ Routes"

if grep -q "Route::get" src/routes/web.php 2>/dev/null; then
    pass "web.php has root route"
else
    fail "web.php has no routes"
fi

for mod in Auth User Permission Notification; do
    for route_type in api.php web.php; do
        if [ -f "src/modules/$mod/Routes/$route_type" ]; then
            pass "$mod/Routes/$route_type exists"
        else
            fail "$mod/Routes/$route_type missing"
        fi
    done
done

# ActivityLog is API-only (no web routes)
if [ -f "src/modules/ActivityLog/Routes/api.php" ]; then
    pass "ActivityLog/Routes/api.php exists"
else
    fail "ActivityLog/Routes/api.php missing"
fi

# Export is API-only (no web routes)
if [ -f "src/modules/Export/Routes/api.php" ]; then
    pass "Export/Routes/api.php exists"
else
    fail "Export/Routes/api.php missing"
fi
echo ""

# ==========================================
# 51. composer.json details
# ==========================================
echo "▸ composer.json details"

if grep -q '"php": "^8.4"' src/composer.json; then
    pass "PHP constraint ^8.4"
else
    warn "PHP constraint not ^8.4"
fi

if grep -q '"optimize-autoloader": true' src/composer.json; then
    pass "optimize-autoloader enabled"
else
    warn "optimize-autoloader not enabled"
fi

for ns in 'Database\\\\Seeders\\\\' 'Database\\\\Factories\\\\'; do
    if grep -q "$ns" src/composer.json; then
        pass "autoload: $ns"
    else
        fail "autoload missing: $ns"
    fi
done

if grep -q '"phpunit/phpunit"' src/composer.json; then
    pass "dev dep: phpunit"
else
    fail "dev dep: phpunit missing"
fi

if grep -q '"laravel/pint"' src/composer.json; then
    pass "dev dep: laravel/pint"
else
    warn "dev dep: laravel/pint missing"
fi
echo ""

# ==========================================
# 52. package.json details
# ==========================================
echo "▸ package.json details"

for dep in "tailwindcss" "@tailwindcss/vite" "laravel-vite-plugin" "axios"; do
    if grep -q "\"$dep\"" src/package.json; then
        pass "dependency: $dep"
    else
        fail "dependency: $dep missing"
    fi
done
echo ""

# ==========================================
# 53. .gitignore completeness (src/)
# ==========================================
echo "▸ .gitignore (src/)"

if [ -f "src/.gitignore" ]; then
    for pattern in "/public/build" "/public/hot" "/public/storage"; do
        if grep -q "$pattern" src/.gitignore; then
            pass "src/.gitignore covers $pattern"
        else
            fail "src/.gitignore missing $pattern"
        fi
    done
else
    fail "src/.gitignore does not exist"
fi
echo ""

# ==========================================
# 54. Cross-file consistency
# ==========================================
echo "▸ Cross-file consistency"

# nginx client_max_body_size matches php post_max_size
NGINX_MAX=$(grep -oE 'client_max_body_size [0-9]+M' docker/nginx/nginx.conf | awk '{print $2}' | head -1)
PHP_POST=$(grep -oE 'post_max_size = [0-9]+M' docker/php/php.local.ini | awk -F= '{print $2}' | tr -d ' ' | head -1)
if [ "$NGINX_MAX" = "$PHP_POST" ]; then
    pass "nginx client_max_body_size ($NGINX_MAX) = php post_max_size ($PHP_POST)"
else
    fail "nginx client_max_body_size ($NGINX_MAX) ≠ php post_max_size ($PHP_POST)"
fi

# Vite inputs exist as actual files
for input in "src/resources/css/app.css" "src/resources/js/app.js"; do
    if [ -f "$input" ]; then
        pass "Vite input $input exists"
    else
        fail "Vite input $input missing (build will fail)"
    fi
done

# Events in EventServiceProvider → event files exist
for event_path in \
    "src/modules/Auth/Events/UserCreated.php" \
    "src/modules/User/Events/UserUpdated.php" \
    "src/modules/User/Events/UserDeleted.php" \
    "src/modules/Permission/Events/RoleAssigned.php" \
    "src/modules/Permission/Events/PermissionCreated.php" \
    "src/modules/Permission/Events/PermissionUpdated.php" \
    "src/modules/Permission/Events/PermissionDeleted.php" \
    "src/modules/Notification/Events/NotificationRead.php" \
    "src/modules/Notification/Events/NotificationDeleted.php"; do
    if [ -f "$event_path" ]; then
        pass "event file $(basename $event_path) exists"
    else
        fail "event file $(basename $event_path) missing"
    fi
done

# Listeners in EventServiceProvider → listener files exist
for listener_path in \
    "src/modules/Auth/Listeners/SendWelcomeEmail.php" \
    "src/modules/Auth/Listeners/LogUserCreation.php" \
    "src/modules/User/Listeners/LogUserUpdate.php" \
    "src/modules/User/Listeners/LogUserDeletion.php" \
    "src/modules/Permission/Listeners/LogRoleChange.php" \
    "src/modules/Permission/Listeners/LogPermissionCreation.php" \
    "src/modules/Permission/Listeners/LogPermissionUpdate.php" \
    "src/modules/Permission/Listeners/LogPermissionDeletion.php" \
    "src/modules/Notification/Listeners/LogNotificationRead.php" \
    "src/modules/Notification/Listeners/LogNotificationDeletion.php"; do
    if [ -f "$listener_path" ]; then
        pass "listener file $(basename $listener_path) exists"
    else
        fail "listener file $(basename $listener_path) missing"
    fi
done

# Seeders referenced in DatabaseSeeder exist
for seeder_path in \
    "src/modules/Permission/Database/Seeders/PermissionSeeder.php" \
    "src/modules/Permission/Database/Seeders/RoleSeeder.php"; do
    if [ -f "$seeder_path" ]; then
        pass "seeder $(basename $seeder_path) exists"
    else
        fail "seeder $(basename $seeder_path) missing (DatabaseSeeder ref)"
    fi
done
echo ""

# ==========================================
# Summary
# ==========================================
TOTAL=$((PASS + FAIL + WARN))
echo "============================================"
echo -e "  ${GREEN}PASS: $PASS${NC}  ${RED}FAIL: $FAIL${NC}  ${YELLOW}WARN: $WARN${NC}  TOTAL: $TOTAL"
echo "============================================"
echo ""

if [ "$FAIL" -gt 0 ]; then
    echo -e "${RED}Some checks failed. Fix the issues above before using this boilerplate.${NC}"
    exit 1
else
    echo -e "${GREEN}All checks passed! Boilerplate is ready for use.${NC}"
    exit 0
fi
