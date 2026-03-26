#!/bin/bash
# ============================================
# Project Rename
# Usage: ./rename.sh <new-project-name>
# Example: ./rename.sh yadah-productions
# ============================================
# Replaces all occurrences of "myproject" with
# the new name in:
#   - .env.example
#   - src/.env.example
#   - README.md
# ============================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERRO]${NC} $1"; exit 1; }

# --- Validar argumento ---
[ -z "$1" ] && error "Uso: ./rename.sh <nome-do-projecto>\nExemplo: ./rename.sh yadah-productions"

NEW_NAME="$1"

# Validar formato: apenas letras minúsculas, números e hífens
if ! echo "$NEW_NAME" | grep -qE '^[a-z0-9][a-z0-9-]*[a-z0-9]$'; then
    error "Nome inválido: '$NEW_NAME'\nUsa apenas letras minúsculas, números e hífens. Ex: yadah-productions"
fi

# Derivar variantes do nome (hífens → underscores para variáveis)
NEW_SLUG=$(echo "$NEW_NAME" | tr '-' '_')
OLD_NAME="myproject"
OLD_SLUG="myproject"

info "A renomear projecto para: $NEW_NAME"
echo ""

# --- Verificar ficheiros existem ---
for f in .env.example src/.env.example README.md; do
    [ -f "$f" ] || error "Ficheiro '$f' não encontrado. Estás na raiz do projecto?"
done

# --- Fazer substituições ---

# .env.example
sed -i \
    -e "s|PROJECT_NAME=${OLD_NAME}|PROJECT_NAME=${NEW_NAME}|g" \
    -e "s|POSTGRES_DB=${OLD_SLUG}_db|POSTGRES_DB=${NEW_SLUG}_db|g" \
    -e "s|POSTGRES_USER=${OLD_SLUG}_user|POSTGRES_USER=${NEW_SLUG}_user|g" \
    .env.example
info ".env.example actualizado"

# src/.env.example
sed -i \
    -e "s|DB_DATABASE=${OLD_SLUG}_db|DB_DATABASE=${NEW_SLUG}_db|g" \
    -e "s|DB_USERNAME=${OLD_SLUG}_user|DB_USERNAME=${NEW_SLUG}_user|g" \
    src/.env.example
info "src/.env.example actualizado"

# README.md — substituir myproject genérico no bloco de exemplo
sed -i "s|PROJECT_NAME=${OLD_NAME}|PROJECT_NAME=${NEW_NAME}|g" README.md
info "README.md actualizado"

# --- Resultado ---
echo ""
echo "============================================"
info "Projecto renomeado para: $NEW_NAME"
echo "============================================"
echo ""
echo "  Próximo passo:"
echo "    ./setup.sh        # dev"
echo "    ./setup.sh --prod # produção"
echo ""
