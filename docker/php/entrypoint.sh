#!/bin/sh
# ============================================
# Meu Projecto — Docker Entrypoint
# ============================================
# Lê Docker secrets e exporta como variáveis
# de ambiente para o Laravel usar.
# ============================================

if [ -f /run/secrets/db_password ]; then
    export DB_PASSWORD=$(cat /run/secrets/db_password)
fi

if [ -f /run/secrets/redis_password ]; then
    export REDIS_PASSWORD=$(cat /run/secrets/redis_password)
fi

exec "$@"
