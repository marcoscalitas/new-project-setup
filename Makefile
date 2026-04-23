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

seed: ## Run database seeders
	$(DC) exec app php artisan db:seed

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
# Database
# ============================================

db-dump: ## Dump database — usage: make db-dump [FILE=backups/custom.sql.gz]
	@mkdir -p backups
	$(eval DUMP_FILE := $(or $(FILE),backups/$(POSTGRES_DB)_$(shell date +%Y%m%d_%H%M%S).sql.gz))
	$(DC) exec -T postgres pg_dump -U $(POSTGRES_USER) $(POSTGRES_DB) | gzip > $(DUMP_FILE)
	@echo "  Backup: $(DUMP_FILE)"

db-restore: ## Restore database from file — usage: make db-restore FILE=backups/dump.sql.gz
	@[ -n "$(FILE)" ] || (echo "  Uso: make db-restore FILE=backups/dump.sql.gz" && exit 1)
	@[ -f "$(FILE)" ] || (echo "  Ficheiro '$(FILE)' não encontrado." && exit 1)
	gunzip -c $(FILE) | $(DC) exec -T postgres psql -U $(POSTGRES_USER) -d $(POSTGRES_DB)
	@echo "  Base de dados restaurada a partir de $(FILE)"

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

reset: ## Destroy containers, volumes, .env files and port registry entry ⚠️
	@read -p "Tens a certeza? Isto apaga volumes e .env files. [y/N] " ans && [ "$$ans" = "y" ]
	@if [ -f .env ]; then \
		$(DC) down -v; \
		if [ -n "$(PROJECT_NAME)" ] && [ -f "$$HOME/.docker-ports-registry" ]; then \
			grep -v "^$(PROJECT_NAME):" "$$HOME/.docker-ports-registry" > "$$HOME/.docker-ports-registry.tmp" 2>/dev/null || true; \
			mv "$$HOME/.docker-ports-registry.tmp" "$$HOME/.docker-ports-registry"; \
			echo "  Projecto '$(PROJECT_NAME)' removido do registo de portas."; \
		fi; \
	else \
		echo "  .env não existe — a ignorar docker compose down."; \
	fi
	rm -f .env src/.env

ports: ## Show all projects and their reserved ports
	@if [ -f "$$HOME/.docker-ports-registry" ] && [ -s "$$HOME/.docker-ports-registry" ]; then \
		echo ""; \
		echo "  Registo global de portas (~/.docker-ports-registry):"; \
		echo ""; \
		awk -F: '{printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' "$$HOME/.docker-ports-registry"; \
		echo ""; \
	else \
		echo "  Nenhum projecto registado."; \
	fi

# ============================================
# Help
# ============================================

help: ## Show this help
	@awk 'BEGIN {FS=":.*## "} /^[a-zA-Z_-]+:.*## / {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.PHONY: setup setup-prod up down restart build ps logs logs-nginx logs-queue \
        shell artisan migrate migrate-fresh seed tinker composer npm \
        cache-clear cache-warm db-dump db-restore test test-unit test-feature reset ports help
