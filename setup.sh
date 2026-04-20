#!/bin/bash
# ============================================
# Project Setup
# Usage: ./setup.sh [--prod]
# ============================================

set -e

PROD=false
CLEANUP_NEEDED=false
for arg in "$@"; do
    [ "$arg" = "--prod" ] && PROD=true
done

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() {
    echo -e "${RED}[ERRO]${NC} $1"
    if $CLEANUP_NEEDED; then
        warn "A limpar containers parciais..."
        if $PROD; then
            docker compose -f docker-compose.yml down 2>/dev/null || true
        else
            docker compose down 2>/dev/null || true
        fi
    fi
    exit 1
}

# --- sed portável (Linux GNU sed vs macOS BSD sed) ---
sedi() {
    if sed --version >/dev/null 2>&1; then
        sed -i "$@"
    else
        sed -i '' "$@"
    fi
}

# --- Lock contra execução concorrente ---
if command -v md5sum >/dev/null 2>&1; then
    LOCKFILE="/tmp/setup-$(echo "$(pwd)" | md5sum | cut -d' ' -f1).lock"
elif command -v md5 >/dev/null 2>&1; then
    LOCKFILE="/tmp/setup-$(echo "$(pwd)" | md5 -q).lock"
else
    LOCKFILE="/tmp/setup-$(echo "$(pwd)" | tr '/' '_').lock"
fi
if command -v flock >/dev/null 2>&1; then
    exec 9>"$LOCKFILE"
    if ! flock -n 9; then
        error "Outro setup já está a correr neste directório."
    fi
fi

# --- Limpeza em caso de interrupção (Ctrl+C) ---
cleanup() {
    echo ""
    warn "Setup interrompido pelo utilizador."
    if $CLEANUP_NEEDED; then
        warn "A parar containers parcialmente criados..."
        if $PROD; then
            docker compose -f docker-compose.yml down 2>/dev/null || true
        else
            docker compose down 2>/dev/null || true
        fi
    fi
    exit 130
}
trap cleanup INT TERM

# --- Verificar se o projecto já foi configurado ---
if [ -f .env ]; then
    warn "Este projecto já foi configurado (.env já existe)."
    warn "Continuar irá sobrescrever as configurações actuais (passwords, portas, etc.)."
    read -rp "Continuar mesmo assim? (s/N): " CONFIRM
    case "$CONFIRM" in
        s|S|sim|SIM) info "A reconfigurar projecto..." ;;
        *) info "Setup cancelado."; exit 0 ;;
    esac
    echo ""
fi

# --- Verificar Docker ---
command -v docker >/dev/null 2>&1 || error "Docker não encontrado. Instale o Docker primeiro."
docker compose version >/dev/null 2>&1 || error "Docker Compose não encontrado."

# Verificar se o Docker daemon está a correr
if ! docker info >/dev/null 2>&1; then
    error "Docker daemon não está a correr.\nInicia com: sudo systemctl start docker"
fi

# Verificar permissões do utilizador
if ! docker ps >/dev/null 2>&1; then
    error "Sem permissões para usar o Docker.\nAdiciona o teu utilizador ao grupo docker: sudo usermod -aG docker \$USER\nDepois faz logout e login novamente."
fi

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

# Validar formato: apenas letras minúsculas, números, hífens e underscores (mínimo 2 caracteres)
if ! echo "$NEW_NAME" | grep -qE '^[a-z0-9]([a-z0-9_-]*[a-z0-9])?$'; then
    error "Nome inválido: '$NEW_NAME'\nUsa apenas letras minúsculas, números, hífens e underscores (mínimo 1 caractere).\nNão pode começar ou terminar com hífen/underscore. Ex: yadah-productions"
fi

NEW_SLUG=$(echo "$NEW_NAME" | tr '-' '_')

# Verificar se já existe um projecto Docker com este nome
if docker compose ls --format json 2>/dev/null | grep -q "\"Name\":\"${NEW_NAME}\""; then
    EXISTING_DIR=$(docker compose ls --format json 2>/dev/null \
        | grep -o "\"Name\":\"${NEW_NAME}\"[^}]*\"ConfigFiles\":\"[^\"]*\"" \
        | grep -o '"ConfigFiles":"[^"]*"' | cut -d'"' -f4 | xargs dirname 2>/dev/null)
    if [ -n "$EXISTING_DIR" ] && [ "$EXISTING_DIR" != "$(pwd)" ]; then
        error "Já existe um projecto Docker com o nome '${NEW_NAME}' em:\n  ${EXISTING_DIR}\n\nEscolhe um nome diferente para evitar conflitos de containers, volumes e rede."
    fi
fi

# Verificar volumes órfãos de projecto anterior com mesmo nome
ORPHAN_VOLS=$(docker volume ls --format '{{.Name}}' 2>/dev/null | grep -E "^${NEW_NAME}_(postgres|redis)_data$" || true)
if [ -n "$ORPHAN_VOLS" ]; then
    warn "Existem volumes de um projecto anterior com o nome '${NEW_NAME}':"
    echo "$ORPHAN_VOLS" | while read -r vol; do echo "  - $vol"; done
    read -rp "Remover volumes antigos? Os dados serão perdidos. (s/N): " REMOVE_VOLS
    case "$REMOVE_VOLS" in
        s|S|sim|SIM)
            for vol in $ORPHAN_VOLS; do
                docker volume rm "$vol" 2>/dev/null && info "Volume '$vol' removido." || warn "Não foi possível remover '$vol' (pode estar em uso)."
            done
            ;;
        *)
            warn "Volumes antigos mantidos — o novo projecto irá usar a base de dados existente."
            ;;
    esac
fi

# Verificar ficheiros necessários para renomeação
for f in .env.example src/.env.example README.md; do
    [ -f "$f" ] || error "Ficheiro '$f' não encontrado. Estás na raiz do projecto?"
done

info "A renomear projecto para: $NEW_NAME"

# --- Copiar templates para ficheiros de trabalho ---
[ ! -f .env.example ] && error "Ficheiro .env.example não encontrado."
[ ! -f src/.env.example ] && error "Ficheiro src/.env.example não encontrado."

cp .env.example .env
chmod 600 .env
info "Ficheiro .env criado a partir do .env.example"

cp src/.env.example src/.env
chmod 600 src/.env
info "Ficheiro src/.env criado a partir do src/.env.example"

# --- Substituir nomes nos ficheiros gerados (nunca nos templates) ---

# .env
sedi \
    -e "s|PROJECT_NAME=${OLD_NAME}|PROJECT_NAME=${NEW_NAME}|g" \
    -e "s|POSTGRES_DB=${OLD_SLUG}_db|POSTGRES_DB=${NEW_SLUG}_db|g" \
    -e "s|POSTGRES_USER=${OLD_SLUG}_user|POSTGRES_USER=${NEW_SLUG}_user|g" \
    .env
info ".env actualizado"

# src/.env
sedi \
    -e "s|DB_DATABASE=${OLD_SLUG}_db|DB_DATABASE=${NEW_SLUG}_db|g" \
    -e "s|DB_USERNAME=${OLD_SLUG}_user|DB_USERNAME=${NEW_SLUG}_user|g" \
    src/.env
info "src/.env actualizado"

echo ""
info "Projecto renomeado para: $NEW_NAME"
echo ""

# --- .env da raiz (Docker) — gerar passwords e configurar ambiente ---

# Definir ambiente
if $PROD; then
    sedi "s|^APP_ENV=.*|APP_ENV=production|" .env
    info "Modo produção activado."
fi

# Auto-gerar passwords seguras se estiverem vazias
if grep -qE '^POSTGRES_PASSWORD=\s*$' .env; then
    GENERATED_PG=$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32)
    sedi "s|^POSTGRES_PASSWORD=.*|POSTGRES_PASSWORD=${GENERATED_PG}|" .env
    info "POSTGRES_PASSWORD gerado automaticamente."
fi
if grep -qE '^REDIS_PASSWORD=\s*$' .env; then
    GENERATED_RD=$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32)
    sedi "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=${GENERATED_RD}|" .env
    info "REDIS_PASSWORD gerado automaticamente."
fi

# --- Carregar variáveis do .env ---
set +e
set -a
. ./.env
set +a
set -e

# --- Validar que o ficheiro PHP ini para o APP_ENV existe ---
PHP_INI="docker/php/php.${APP_ENV:-local}.ini"
if [ ! -f "$PHP_INI" ]; then
    error "Ficheiro '$PHP_INI' não encontrado.\nValores válidos de APP_ENV: local, production (correspondem a docker/php/php.*.ini)."
fi

# Comando docker compose para o modo actual
if $PROD; then
    DCMD="docker compose -f docker-compose.yml"
else
    DCMD="docker compose"
fi

# --- src/.env — sincronizar credenciais e configurar ambiente ---
sedi "s|^DB_DATABASE=.*|DB_DATABASE=${POSTGRES_DB}|" src/.env
sedi "s|^DB_USERNAME=.*|DB_USERNAME=${POSTGRES_USER}|" src/.env
sedi "s|^DB_PASSWORD=.*|DB_PASSWORD=${POSTGRES_PASSWORD}|" src/.env
sedi "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=${REDIS_PASSWORD}|" src/.env
sedi "s|^APP_URL=.*|APP_URL=http://localhost:${APP_PORT:-8080}|" src/.env

if $PROD; then
    sedi "s|^APP_ENV=.*|APP_ENV=production|" src/.env
    sedi "s|^APP_DEBUG=.*|APP_DEBUG=false|" src/.env
    sedi "s|^LOG_LEVEL=.*|LOG_LEVEL=error|" src/.env
    warn "SESSION_SECURE_COOKIE=false — activa manualmente se usares HTTPS (reverse proxy)."
fi

info "src/.env sincronizado com as credenciais do .env"

# --- Verificar e resolver portas disponíveis ---
is_port_in_use() {
    local port=$1
    local project=$2
    # Verifica se a porta está em uso (ss → lsof fallback para macOS)
    local port_in_use=false
    if command -v ss >/dev/null 2>&1; then
        ss -tln 2>/dev/null | awk '{print $4}' | grep -qE ":${port}$" && port_in_use=true
    elif command -v lsof >/dev/null 2>&1; then
        lsof -iTCP:"$port" -sTCP:LISTEN -P -n >/dev/null 2>&1 && port_in_use=true
    elif command -v netstat >/dev/null 2>&1; then
        netstat -tln 2>/dev/null | awk '{print $4}' | grep -qE ":${port}$" && port_in_use=true
    fi
    if [ "$port_in_use" = false ]; then
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

find_free_port() {
    local port=$1
    local max_attempts=50
    local attempt=0
    while [ $attempt -lt $max_attempts ]; do
        if ! is_port_in_use "$port" "$PROJECT_NAME"; then
            echo "$port"
            return 0
        fi
        port=$((port + 1))
        # Evitar portas acima do intervalo válido
        if [ "$port" -gt 65535 ]; then
            return 1
        fi
        attempt=$((attempt + 1))
    done
    return 1
}

PORTS_TO_CHECK="APP_PORT:${APP_PORT:-8080}:Nginx"
PORTS_TO_CHECK="${PORTS_TO_CHECK} REDIS_PORT:${REDIS_PORT:-6379}:Redis"

if ! $PROD; then
    PORTS_TO_CHECK="${PORTS_TO_CHECK} VITE_PORT:${VITE_PORT:-5173}:Node/Vite"
    PORTS_TO_CHECK="${PORTS_TO_CHECK} MAILPIT_PORT:${MAILPIT_PORT:-8025}:Mailpit-UI"
    PORTS_TO_CHECK="${PORTS_TO_CHECK} MAILPIT_SMTP_PORT:${MAILPIT_SMTP_PORT:-1025}:Mailpit-SMTP"
fi

REASSIGNED=""
for entry in $PORTS_TO_CHECK; do
    VAR_NAME=$(echo "$entry" | cut -d: -f1)
    PORT_NUM=$(echo "$entry" | cut -d: -f2)
    SERVICE=$(echo "$entry" | cut -d: -f3)
    if is_port_in_use "$PORT_NUM" "$PROJECT_NAME"; then
        NEW_PORT=$(find_free_port "$((PORT_NUM + 1))")
        if [ -z "$NEW_PORT" ]; then
            error "Não foi possível encontrar uma porta livre para ${SERVICE} (a partir de ${PORT_NUM})."
        fi
        warn "Porta ${PORT_NUM} (${SERVICE}) ocupada — a usar porta ${NEW_PORT}"
        # Actualizar no .env
        sedi "s|^${VAR_NAME}=.*|${VAR_NAME}=${NEW_PORT}|" .env
        # Actualizar variável em memória
        eval "${VAR_NAME}=${NEW_PORT}"
        REASSIGNED="${REASSIGNED}  ${SERVICE}: ${PORT_NUM} → ${NEW_PORT}\n"
    fi
done

# Sincronizar APP_URL com a porta final do APP_PORT
sedi "s|^APP_URL=.*|APP_URL=http://localhost:${APP_PORT}|" src/.env

if [ -n "$REASSIGNED" ]; then
    echo ""
    info "Portas reatribuídas automaticamente:"
    echo -e "$REASSIGNED"
fi

info "Todas as portas estão disponíveis."

# --- Verificar espaço em disco ---
AVAILABLE_MB=$(df -m . 2>/dev/null | awk 'NR==2 {print $4}')
if [ -n "$AVAILABLE_MB" ] && [ "$AVAILABLE_MB" -lt 2048 ]; then
    warn "Espaço em disco baixo: ${AVAILABLE_MB}MB disponíveis (mínimo recomendado: 2GB)."
    read -rp "Continuar mesmo assim? (s/N): " CONFIRM_DISK
    case "$CONFIRM_DISK" in
        s|S|sim|SIM) ;;
        *) info "Setup cancelado."; exit 0 ;;
    esac
fi

# --- Verificar acesso à internet ---
if ! curl -s --max-time 5 --head https://registry-1.docker.io >/dev/null 2>&1 && \
   ! wget -q --spider --timeout=5 https://registry-1.docker.io 2>/dev/null; then
    warn "Sem acesso à internet ou ao Docker Hub."
    warn "O build poderá falhar se as imagens não estiverem em cache local."
    read -rp "Continuar mesmo assim? (s/N): " CONFIRM_NET
    case "$CONFIRM_NET" in
        s|S|sim|SIM) ;;
        *) info "Setup cancelado."; exit 0 ;;
    esac
fi

# --- Parar containers anteriores do mesmo projecto ---
if $DCMD ps -q 2>/dev/null | grep -q .; then
    warn "Containers do projecto já estão a correr. A parar antes de reconstruir..."
    $DCMD down 2>/dev/null || true
fi

# --- Subir containers ---
SETUP_START=$(date +%s)
CLEANUP_NEEDED=true

# Passar UID/GID do host para evitar problemas de permissões nos volumes
# UID é read-only no Bash — exportar sem reatribuir
export UID
export GID=$(id -g)

info "A construir e iniciar os containers (UID=${UID}, GID=${GID})..."
if ! $DCMD up -d --build; then
    error "Falha ao construir/iniciar os containers.\nExecuta '$DCMD logs' para ver os erros."
fi

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

# --- Extrair assets Vite compilados (produção) ---
# Em prod, os assets são compilados no Dockerfile (node-builder stage)
# mas o volume ./src:/var/www cobre-os. Copiamos da imagem para o host.
if $PROD; then
    info "A extrair assets compilados da imagem..."
    docker cp "${PROJECT_NAME}-app:/var/www/public/build" ./src/public/build 2>/dev/null \
        && info "Assets Vite copiados para src/public/build/" \
        || warn "Não foi possível extrair assets Vite (pode não haver build configurado)."
fi

# --- Instalar dependências PHP ---
info "A instalar dependências do Composer..."
if $PROD; then
    if ! $DCMD exec -T app composer install --no-interaction --no-dev --optimize-autoloader; then
        error "Falha ao instalar dependências do Composer.\nExecuta '$DCMD exec app composer install' manualmente para ver os erros."
    fi
else
    if ! $DCMD exec -T app composer install --no-interaction --prefer-dist; then
        error "Falha ao instalar dependências do Composer.\nExecuta '$DCMD exec app composer install' manualmente para ver os erros."
    fi
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
if ! $DCMD exec -T app php artisan migrate --force; then
    error "Falha ao executar migrations.\nVerifica a ligação à base de dados e executa '$DCMD exec app php artisan migrate' manualmente."
fi

# --- Seed (apenas em desenvolvimento) ---
if $PROD; then
    warn "Seed NÃO executado em produção (credenciais padrão são inseguras)."
    warn "Para popular a base de dados manualmente: $DCMD exec app php artisan db:seed"
else
    info "A popular base de dados (seed)..."
    $DCMD exec -T app php artisan db:seed --force || warn "Seed falhou — podes executar manualmente: $DCMD exec app php artisan db:seed"
fi

# --- Passport: chaves de criptografia e client ---
info "A configurar Passport..."
$DCMD exec -T app php artisan passport:keys --force
# Verificar se Personal Access Client já existe antes de criar
HAS_CLIENT=$($DCMD exec -T app php artisan tinker --execute="echo \Laravel\Passport\Client::where('personal_access_client', true)->count();" 2>/dev/null | grep -E '^[0-9]+$' | tail -1)
if [ "${HAS_CLIENT:-0}" = "0" ]; then
    $DCMD exec -T app php artisan passport:client --personal --name="Personal Access Client" --no-interaction 2>&1 || true
    info "Personal Access Client criado."
else
    info "Personal Access Client já existente — a ignorar."
fi

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
CLEANUP_NEEDED=false
SETUP_END=$(date +%s)
SETUP_DURATION=$(( SETUP_END - SETUP_START ))
SETUP_MIN=$(( SETUP_DURATION / 60 ))
SETUP_SEC=$(( SETUP_DURATION % 60 ))

echo ""
echo "============================================"
info "Setup concluído com sucesso! (${SETUP_MIN}m ${SETUP_SEC}s)"
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
