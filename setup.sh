#!/bin/bash
# ============================================
# Project Setup
# Usage: ./setup.sh [--prod]
# ============================================

set -e

PROD=false
for arg in "$@"; do
    [ "$arg" = "--prod" ] && PROD=true
done

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERRO]${NC} $1"; exit 1; }

# --- Renomear Projecto ---
OLD_NAME="myproject"
OLD_SLUG="myproject"

echo ""
echo "============================================"
echo "  Configuração do Projecto"
echo "============================================"
echo ""
read -rp "Nome do projecto: " NEW_NAME

# Validar que não está vazio
[ -z "$NEW_NAME" ] && error "Nome do projecto não pode estar vazio."

# Validar formato: apenas letras minúsculas, números, hífens e underscores
if ! echo "$NEW_NAME" | grep -qE '^[a-z0-9][a-z0-9_-]*[a-z0-9]$'; then
    error "Nome inválido: '$NEW_NAME'\nUsa apenas letras minúsculas, números, hífens e underscores. Ex: yadah-productions"
fi

NEW_SLUG=$(echo "$NEW_NAME" | tr '-' '_')

# Verificar ficheiros necessários para renomeação
for f in .env.example src/.env.example README.md; do
    [ -f "$f" ] || error "Ficheiro '$f' não encontrado. Estás na raiz do projecto?"
done

info "A renomear projecto para: $NEW_NAME"

# --- Copiar templates para ficheiros de trabalho ---
[ ! -f .env.example ] && error "Ficheiro .env.example não encontrado."
[ ! -f src/.env.example ] && error "Ficheiro src/.env.example não encontrado."

cp .env.example .env
info "Ficheiro .env criado a partir do .env.example"

cp src/.env.example src/.env
info "Ficheiro src/.env criado a partir do src/.env.example"

# --- Substituir nomes nos ficheiros gerados (nunca nos templates) ---

# .env
sed -i \
    -e "s|PROJECT_NAME=${OLD_NAME}|PROJECT_NAME=${NEW_NAME}|g" \
    -e "s|POSTGRES_DB=${OLD_SLUG}_db|POSTGRES_DB=${NEW_SLUG}_db|g" \
    -e "s|POSTGRES_USER=${OLD_SLUG}_user|POSTGRES_USER=${NEW_SLUG}_user|g" \
    .env
info ".env actualizado"

# src/.env
sed -i \
    -e "s|DB_DATABASE=${OLD_SLUG}_db|DB_DATABASE=${NEW_SLUG}_db|g" \
    -e "s|DB_USERNAME=${OLD_SLUG}_user|DB_USERNAME=${NEW_SLUG}_user|g" \
    src/.env
info "src/.env actualizado"

echo ""
info "Projecto renomeado para: $NEW_NAME"
echo ""

# --- Verificar Docker ---
command -v docker >/dev/null 2>&1 || error "Docker não encontrado. Instale o Docker primeiro."
docker compose version >/dev/null 2>&1 || error "Docker Compose não encontrado."

# --- .env da raiz (Docker) — gerar passwords e configurar ambiente ---

# Definir ambiente
if $PROD; then
    sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
    info "Modo produção activado."
fi

# Auto-gerar passwords seguras se estiverem vazias
if grep -qE '^POSTGRES_PASSWORD=\s*$' .env; then
    GENERATED_PG=$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32)
    sed -i "s|^POSTGRES_PASSWORD=.*|POSTGRES_PASSWORD=${GENERATED_PG}|" .env
    info "POSTGRES_PASSWORD gerado automaticamente."
fi
if grep -qE '^REDIS_PASSWORD=\s*$' .env; then
    GENERATED_RD=$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32)
    sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=${GENERATED_RD}|" .env
    info "REDIS_PASSWORD gerado automaticamente."
fi

# --- Carregar variáveis do .env ---
set -a
. ./.env
set +a

# Comando docker compose para o modo actual
if $PROD; then
    DCMD="docker compose -f docker-compose.yml"
else
    DCMD="docker compose"
fi

# --- src/.env — sincronizar credenciais e configurar ambiente ---
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${POSTGRES_DB}|" src/.env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${POSTGRES_USER}|" src/.env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${POSTGRES_PASSWORD}|" src/.env
sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=${REDIS_PASSWORD}|" src/.env
sed -i "s|^APP_URL=.*|APP_URL=http://localhost:${APP_PORT:-8080}|" src/.env

if $PROD; then
    sed -i "s|^APP_ENV=.*|APP_ENV=production|" src/.env
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" src/.env
    sed -i "s|^LOG_LEVEL=.*|LOG_LEVEL=error|" src/.env
    sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" src/.env
fi

info "src/.env sincronizado com as credenciais do .env"

# --- Verificar portas disponíveis ---
check_port() {
    local port=$1
    local project=$2
    # Verifica se a porta está em uso
    if ! ss -tln 2>/dev/null | awk '{print $4}' | grep -qE ":${port}$"; then
        return 1 # porta livre
    fi
    # Se a porta está em uso, verifica se é um container do próprio projecto
    local container_id
    container_id=$(docker ps --filter "publish=${port}" --format '{{.Names}}' 2>/dev/null)
    if [ -n "$container_id" ] && echo "$container_id" | grep -q "^${project}-"; then
        return 1 # porta usada pelo próprio projecto — ignorar
    fi
    return 0 # porta em uso por outro processo
}

PORTS_TO_CHECK="APP_PORT:${APP_PORT:-8080}:Nginx"
PORTS_TO_CHECK="${PORTS_TO_CHECK} REDIS_PORT:${REDIS_PORT:-6379}:Redis"

if ! $PROD; then
    PORTS_TO_CHECK="${PORTS_TO_CHECK} VITE_PORT:${VITE_PORT:-5173}:Node/Vite"
    PORTS_TO_CHECK="${PORTS_TO_CHECK} MAILPIT_PORT:${MAILPIT_PORT:-8025}:Mailpit-UI"
    PORTS_TO_CHECK="${PORTS_TO_CHECK} MAILPIT_SMTP_PORT:${MAILPIT_SMTP_PORT:-1025}:Mailpit-SMTP"
fi

CONFLICTS=""
for entry in $PORTS_TO_CHECK; do
    VAR_NAME=$(echo "$entry" | cut -d: -f1)
    PORT_NUM=$(echo "$entry" | cut -d: -f2)
    SERVICE=$(echo "$entry" | cut -d: -f3)
    if check_port "$PORT_NUM" "$PROJECT_NAME"; then
        CONFLICTS="${CONFLICTS}  - Porta ${PORT_NUM} (${SERVICE}) já está em uso — variável: ${VAR_NAME}\n"
    fi
done

if [ -n "$CONFLICTS" ]; then
    echo ""
    warn "Conflitos de portas detectados:"
    echo -e "$CONFLICTS"
    warn "Ajuste as portas no ficheiro .env e execute novamente."
    echo ""
    exit 1
fi

info "Todas as portas estão disponíveis."

# --- Subir containers ---
info "A construir e iniciar os containers..."
$DCMD up -d --build

# --- Aguardar serviços ficarem healthy ---
info "A aguardar que os serviços fiquem prontos..."
TIMEOUT=120
ELAPSED=0
while [ $ELAPSED -lt $TIMEOUT ]; do
    APP_HEALTH=$(docker inspect --format='{{.State.Health.Status}}' "${PROJECT_NAME:-myproject}-app" 2>/dev/null || echo "starting")
    if [ "$APP_HEALTH" = "healthy" ]; then
        break
    fi
    sleep 3
    ELAPSED=$((ELAPSED + 3))
    printf "."
done
echo ""

if [ $ELAPSED -ge $TIMEOUT ]; then
    warn "Timeout a aguardar serviços. Alguns podem não estar prontos."
    $DCMD ps
fi

# --- Instalar dependências PHP ---
info "A instalar dependências do Composer..."
if $PROD; then
    $DCMD exec -T app composer install --no-interaction --no-dev --optimize-autoloader
else
    $DCMD exec -T app composer install --no-interaction --prefer-dist
fi

# --- Gerar APP_KEY (só se estiver vazio) ---
if grep -q '^APP_KEY=$' src/.env; then
    info "A gerar chave da aplicação..."
    $DCMD exec -T app php artisan key:generate
else
    info "APP_KEY já definida — a ignorar key:generate."
fi

# --- Executar migrations ---
info "A executar migrations..."
$DCMD exec -T app php artisan migrate --force

# --- Passport: chaves de criptografia e client ---
info "A configurar Passport..."
$DCMD exec -T app php artisan passport:keys --force
$DCMD exec -T app php artisan passport:client --personal --name="Personal Access Client" --no-interaction

# --- Cache de produção ---
if $PROD; then
    info "A optimizar para produção..."
    $DCMD exec -T app php artisan config:cache
    $DCMD exec -T app php artisan route:cache
    $DCMD exec -T app php artisan view:cache
fi

# --- Storage link ---
info "A criar symlink do storage..."
$DCMD exec -T app php artisan storage:link 2>/dev/null || true

# --- Resultado ---
echo ""
echo "============================================"
info "Setup concluído com sucesso!"
echo "============================================"
echo ""
echo "  App: http://localhost:${APP_PORT:-8080}"
if ! $PROD; then
    echo "  Mailpit: http://localhost:${MAILPIT_PORT:-8025}"
    echo "  Vite:    http://localhost:${VITE_PORT:-5173}"
fi
echo ""
echo "  Comandos úteis:"
echo "    $DCMD exec app sh          # Entrar no container"
echo "    $DCMD exec app php artisan # Artisan"
echo "    $DCMD logs -f app          # Logs"
echo "    $DCMD down                 # Parar"
echo ""
