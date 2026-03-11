-- ============================================
-- Meu Projecto — PostgreSQL Initialization
-- ============================================

-- --- Extensões ---
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";      -- gerar UUIDs
CREATE EXTENSION IF NOT EXISTS "unaccent";        -- pesquisa sem acentos
CREATE EXTENSION IF NOT EXISTS "pg_trgm";         -- pesquisa por similaridade

-- --- Timezone ---
ALTER DATABASE meu_projecto_db SET timezone TO 'Africa/Luanda';

-- --- Permissões ---
GRANT ALL PRIVILEGES ON DATABASE meu_projecto_db TO meu_projecto_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO meu_projecto_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO meu_projecto_user;