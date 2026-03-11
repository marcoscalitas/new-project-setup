#!/bin/bash
# ============================================
# Meu Projecto — PostgreSQL Initialization
# ============================================

psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" <<-EOSQL
    -- --- Extensões ---
    CREATE EXTENSION IF NOT EXISTS "uuid-ossp";      -- gerar UUIDs
    CREATE EXTENSION IF NOT EXISTS "unaccent";        -- pesquisa sem acentos
    CREATE EXTENSION IF NOT EXISTS "pg_trgm";         -- pesquisa por similaridade

    -- --- Timezone ---
    ALTER DATABASE "$POSTGRES_DB" SET timezone TO 'Africa/Luanda';
EOSQL
