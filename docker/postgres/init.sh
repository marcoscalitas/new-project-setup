#!/bin/bash
# ============================================
# PostgreSQL Initialization
# ============================================

psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" <<-EOSQL
    -- --- Extensions ---
    CREATE EXTENSION IF NOT EXISTS "uuid-ossp";  -- UUID generation
    CREATE EXTENSION IF NOT EXISTS "unaccent";   -- accent-insensitive search
    CREATE EXTENSION IF NOT EXISTS "pg_trgm";    -- trigram similarity search
EOSQL
