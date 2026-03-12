#!/bin/bash
# ============================================
# Meu Projecto — Setup Automático
# ============================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERRO]${NC} $1"; exit 1; }

# --- Verificar Docker ---
command -v docker >/dev/null 2>&1 || error "Docker não encontrado. Instale o Docker primeiro."
docker compose version >/dev/null 2>&1 || error "Docker Compose não encontrado."

# --- .env da raiz (Docker) ---
if [ ! -f .env ]; then
    if [ ! -f .env.example ]; then
        error "Ficheiro .env.example não encontrado."
    fi
    cp .env.example .env
    info "Ficheiro .env criado a partir do .env.example"
else
    info "Ficheiro .env já existe — a usar configuração existente."
fi

# --- Docker Secrets ---
mkdir -p secrets

if [ ! -f secrets/db_password ]; then
    tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32 > secrets/db_password
    info "Secret db_password gerado automaticamente (32 caracteres aleatórios)."
else
    info "Secret db_password já existe."
fi

if [ ! -f secrets/redis_password ]; then
    tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32 > secrets/redis_password
    info "Secret redis_password gerado automaticamente (32 caracteres aleatórios)."
else
    info "Secret redis_password já existe."
fi

chmod 600 secrets/db_password secrets/redis_password

# --- Carregar variáveis do .env ---
export $(grep -v '^#' .env | grep -v '^\s*$' | xargs)

# --- .env do Laravel (src/) ---
if [ ! -f src/.env ]; then
    if [ ! -f src/.env.example ]; then
        error "Ficheiro src/.env.example não encontrado."
    fi
    cp src/.env.example src/.env

    # Sincronizar valores do .env da raiz para o src/.env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${POSTGRES_DB}|" src/.env
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${POSTGRES_USER}|" src/.env
    sed -i "s|^APP_URL=.*|APP_URL=http://localhost:${APP_PORT:-8080}|" src/.env

    # Sincronizar passwords a partir dos Docker secrets
    if [ -f secrets/db_password ]; then
        DB_SECRET=$(cat secrets/db_password)
        sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_SECRET}|" src/.env
    fi
    if [ -f secrets/redis_password ]; then
        REDIS_SECRET=$(cat secrets/redis_password)
        sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=${REDIS_SECRET}|" src/.env
    fi

    info "Ficheiro src/.env criado e sincronizado com as credenciais do .env"
else
    info "Ficheiro src/.env já existe — a usar configuração existente."
fi

# --- Subir containers ---
info "A construir e iniciar os containers..."
docker compose up -d --build

# --- Aguardar serviços ficarem healthy ---
info "A aguardar que os serviços fiquem prontos..."
TIMEOUT=120
ELAPSED=0
while [ $ELAPSED -lt $TIMEOUT ]; do
    HEALTHY=$(docker compose ps --format json 2>/dev/null | grep -c '"healthy"' || true)
    TOTAL=$(docker compose ps --format json 2>/dev/null | grep -c '"running"' || true)
    if [ "$HEALTHY" -ge 4 ] 2>/dev/null; then
        break
    fi
    sleep 3
    ELAPSED=$((ELAPSED + 3))
    printf "."
done
echo ""

if [ $ELAPSED -ge $TIMEOUT ]; then
    warn "Timeout a aguardar serviços. Alguns podem não estar prontos."
    docker compose ps
fi

# --- Instalar dependências PHP ---
info "A instalar dependências do Composer..."
docker compose exec -T app composer install --no-interaction --prefer-dist

# --- Gerar APP_KEY ---
info "A gerar chave da aplicação..."
docker compose exec -T app php artisan key:generate

# --- Executar migrations ---
info "A executar migrations..."
docker compose exec -T app php artisan migrate --force

# --- Storage link ---
info "A criar symlink do storage..."
docker compose exec -T app php artisan storage:link 2>/dev/null || true

# --- Resultado ---
echo ""
echo "============================================"
info "Setup concluído com sucesso!"
echo "============================================"
echo ""
echo "  App:     http://localhost:${APP_PORT:-8080}"
echo "  Mailpit: http://localhost:${MAILPIT_PORT:-8025}"
echo "  Vite:    http://localhost:${VITE_PORT:-5173}"
echo ""
echo "  Comandos úteis:"
echo "    docker compose exec app sh          # Entrar no container"
echo "    docker compose exec app php artisan # Artisan"
echo "    docker compose logs -f app          # Logs"
echo "    docker compose down                 # Parar"
echo ""
