# ============================================
# Makefile — shortcuts for daily development
# ============================================
# Usage: make <target>
# ============================================

-include .env
export

# Detect prod vs dev
ifeq ($(APP_ENV),production)
  DC = docker compose -f docker-compose.yml
else
  DC = docker compose
endif

.DEFAULT_GOAL := help

# ============================================
# Setup
# ============================================

setup: ## First-time setup (dev)
	@bash setup.sh

setup-prod: ## First-time setup (production)
	@bash setup.sh --prod

# ============================================
# Containers
# ============================================

up: ## Start all containers
	$(DC) up -d

down: ## Stop all containers (keep volumes)
	$(DC) down

restart: ## Restart all containers
	$(DC) restart

build: ## Rebuild app image and restart
	$(DC) up -d --build app

ps: ## Show container status
	$(DC) ps

logs: ## Follow app logs
	$(DC) logs -f app

logs-nginx: ## Follow nginx logs
	$(DC) logs -f nginx

logs-queue: ## Follow queue logs
	$(DC) logs -f queue

# ============================================
# Application
# ============================================

shell: ## Open shell in app container
	$(DC) exec app sh

artisan: ## Run artisan command — usage: make artisan CMD="migrate"
	$(DC) exec app php artisan $(CMD)

migrate: ## Run database migrations
	$(DC) exec app php artisan migrate

migrate-fresh: ## Drop all tables and re-run migrations ⚠️
	$(DC) exec app php artisan migrate:fresh

tinker: ## Open Laravel Tinker
	$(DC) exec app php artisan tinker

composer: ## Run composer command — usage: make composer CMD="require vendor/pkg"
	$(DC) exec app composer $(CMD)

# ============================================
# Assets (dev only)
# ============================================

npm: ## Run npm command — usage: make npm CMD="install"
	$(DC) exec node npm $(CMD)

# ============================================
# Cache
# ============================================

cache-clear: ## Clear all application caches
	$(DC) exec app php artisan cache:clear
	$(DC) exec app php artisan config:clear
	$(DC) exec app php artisan route:clear
	$(DC) exec app php artisan view:clear

cache-warm: ## Cache config, routes and views (production)
	$(DC) exec app php artisan config:cache
	$(DC) exec app php artisan route:cache
	$(DC) exec app php artisan view:cache

# ============================================
# Testing
# ============================================

test: ## Run all tests
	$(DC) exec app php artisan test

test-unit: ## Run unit tests only
	$(DC) exec app php artisan test --testsuite=Unit

test-feature: ## Run feature tests only
	$(DC) exec app php artisan test --testsuite=Feature

# ============================================
# Maintenance
# ============================================

reset: ## Destroy containers, volumes and .env files ⚠️
	@read -p "Tens a certeza? Isto apaga volumes e .env files. [y/N] " ans && [ "$$ans" = "y" ]
	@if [ -f .env ]; then \
		$(DC) down -v; \
	else \
		echo "  .env não existe — a ignorar docker compose down."; \
	fi
	rm -f .env src/.env

# ============================================
# Help
# ============================================

help: ## Show this help
	@awk 'BEGIN {FS=":.*## "} /^[a-zA-Z_-]+:.*## / {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.PHONY: setup setup-prod up down restart build ps logs logs-nginx logs-queue \
        shell artisan migrate migrate-fresh tinker composer npm \
        cache-clear cache-warm test test-unit test-feature reset help
