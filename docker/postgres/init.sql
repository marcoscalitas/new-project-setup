-- ============================================
-- Yadah Burger — PostgreSQL Initialization
-- ============================================

-- --- Extensões ---
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";      -- gerar UUIDs
CREATE EXTENSION IF NOT EXISTS "unaccent";        -- pesquisa sem acentos
CREATE EXTENSION IF NOT EXISTS "pg_trgm";         -- pesquisa por similaridade

-- --- Timezone ---
ALTER DATABASE yadah_burger_db SET timezone TO 'Africa/Luanda';

-- --- Permissões ---
GRANT ALL PRIVILEGES ON DATABASE yadah_burger_db TO yadah_burger_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO yadah_burger_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO yadah_burger_user;