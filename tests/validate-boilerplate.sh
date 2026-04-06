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

# Dev CSP — allows localhost
DEV_CSP=$(grep "Content-Security-Policy" docker/nginx/nginx.dev.conf || true)
if echo "$DEV_CSP" | grep -q "localhost"; then
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

MODULES=("Auth" "User" "Permission" "Notification")
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
for suite in "Auth-Web" "Auth-Api" "User" "Permission" "Notification" "Unit" "Feature"; do
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
for pkg in "laravel/framework" "laravel/passport" "laravel/fortify" "spatie/laravel-permission"; do
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
# 18. README cross-reference
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
