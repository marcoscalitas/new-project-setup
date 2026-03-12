# 🐳 Laravel Docker Boilerplate

Boilerplate Docker para projectos **Laravel 12 + PHP 8.4** com Nginx, PostgreSQL, Redis, Node.js e Mailpit.  
Clone, configure e comece a desenvolver em minutos.

---

## Stack

| Serviço    | Imagem / Versão       | Porta padrão |
|------------|-----------------------|--------------|
| PHP-FPM    | `php:8.4-fpm-alpine`  | 9000 (interno) |
| Nginx      | `nginx:alpine`        | `APP_PORT` → 80 |
| PostgreSQL | `postgres:17-alpine`  | 5432 |
| Redis      | `redis:alpine`        | 6379 |
| Queue      | PHP 8.4 (worker)      | — |
| Scheduler  | PHP 8.4 (cron)        | — |
| Node       | `node:22-alpine`      | `VITE_PORT` → 5173 |
| Mailpit    | `axllent/mailpit`     | `MAILPIT_PORT` → 8025 (web) / 1025 (SMTP) |

---

## Estrutura do Projecto

```
├── docker-compose.yml              # Serviços base (produção)
├── docker-compose.override.yml     # Serviços dev (node + mailpit) — carregado automaticamente
├── setup.sh                        # Script de setup automático
├── secrets/                        # Docker secrets (passwords)
│   ├── db_password.example          # Template da password do PostgreSQL
│   └── redis_password.example       # Template da password do Redis
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf              # Nginx global (produção — CSP restrito)
│   │   ├── nginx.dev.conf          # Nginx global (dev — CSP com Vite)
│   │   └── default.conf            # Server block (virtual host)
│   ├── php/
│   │   ├── Dockerfile              # Multi-stage build (builder + production)
│   │   ├── entrypoint.sh           # Lê Docker secrets para variáveis de ambiente
│   │   └── php.ini                 # Configurações customizadas do PHP
│   ├── postgres/
│   │   └── init.sh                 # Script de inicialização do banco
│   └── redis/
│       └── redis.conf              # Configuração do Redis
├── src/                            # Código-fonte Laravel
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── ...
└── README.md
```

---

## Início Rápido

### Opção A — Setup automático

```bash
git clone <url-do-repositorio> meu-projecto
cd meu-projecto
./setup.sh
```

O script cria os `.env`, gera passwords aleatórias nos Docker secrets, sobe os containers, instala dependências, gera a chave e executa as migrations automaticamente.

### Opção B — Setup manual

### 1. Clonar o repositório

```bash
git clone <url-do-repositorio> meu-projecto
cd meu-projecto
```

### 2. Configurar variáveis de ambiente

Crie um ficheiro `.env` na raiz do projecto (junto ao `docker-compose.yml`):

```env
# App
APP_PORT=8080

# PostgreSQL
POSTGRES_DB=meu_projecto_db
POSTGRES_USER=meu_projecto_user
```

### 3. Criar Docker secrets

As passwords ficam em ficheiros separados (mais seguro que variáveis de ambiente):

```bash
mkdir -p secrets
echo -n "sua_senha_postgres" > secrets/db_password
echo -n "sua_senha_redis" > secrets/redis_password
chmod 600 secrets/db_password secrets/redis_password
```

### 4. Configurar `.env` do Laravel

Configure o `src/.env`:

```env
APP_NAME="Meu Projecto"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=meu_projecto_db
DB_USERNAME=meu_projecto_user
DB_PASSWORD=sua_senha_postgres

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=sua_senha_redis
REDIS_PORT=6379

# Bases de dados Redis
REDIS_CACHE_DB=0
REDIS_QUEUE_DB=1
REDIS_SESSION_DB=2
```

> **Nota**: dentro dos containers Docker, o `entrypoint.sh` sobrescreve `DB_PASSWORD` e `REDIS_PASSWORD` automaticamente a partir dos Docker secrets.

### 5. Subir os containers

```bash
docker compose up -d --build
```

### 6. Instalar dependências e configurar o Laravel

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 7. Acessar a aplicação

```
http://localhost:8080
```

---

## ⚙️ Checklist de Setup — O que alterar em cada novo projecto

Ao clonar este boilerplate para um novo projecto, substitua todos os valores genéricos (`meu_projecto`, `Meu Projecto`, etc.) pelos dados reais do seu projecto. Abaixo, cada ficheiro está listado com **o quê**, **o valor actual no template** e **o que colocar no lugar**.

> **Dica**: use *Find & Replace* no editor com `meu_projecto` para encontrar rapidamente todos os pontos.

---

### `.env` (criar a partir do `.env.example`)

Copie o `.env.example` para `.env` e preencha:

```bash
cp .env.example .env
```

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| `APP_PORT` | `8080` | Porta HTTP do projecto |
| `POSTGRES_DB` | `meu_projecto_db` | Nome do banco de dados |
| `POSTGRES_USER` | `meu_projecto_user` | Utilizador do PostgreSQL |
| `VITE_PORT` | `5173` | Porta do Vite (opcional) |
| `MAILPIT_PORT` | `8025` | Porta da interface do Mailpit (opcional) |

> As passwords já não ficam no `.env` — são geridas via Docker secrets (ver abaixo).

---

### `.env.example`

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |
| `POSTGRES_DB` | `meu_projecto_db` | Mesmo valor que usará no `.env` |
| `POSTGRES_USER` | `meu_projecto_user` | Mesmo valor que usará no `.env` |

---

### `secrets/` (Docker Secrets)

As passwords do PostgreSQL e Redis são geridas via Docker secrets. Cada ficheiro contém apenas a password (sem newline extra).

| Ficheiro | Contém | Alterar para |
|----------|--------|-------------|
| `secrets/db_password` | Password do PostgreSQL | Uma senha segura (gerada automaticamente pelo `setup.sh`) |
| `secrets/redis_password` | Password do Redis | Uma senha segura (gerada automaticamente pelo `setup.sh`) |

> **setup.sh** gera passwords aleatórias de 32 caracteres automaticamente.
> Para definir manualmente, crie os ficheiros antes de executar o setup:
> ```bash
> mkdir -p secrets
> echo -n "minha_senha_postgres" > secrets/db_password
> echo -n "minha_senha_redis" > secrets/redis_password
> chmod 600 secrets/db_password secrets/redis_password
> ```
>
> Os ficheiros `secrets/*.example` são templates tracked pelo git. Os ficheiros reais estão no `.gitignore`.

---

### `.gitignore`

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |

---

### `docker-compose.yml` (base)

Contém os serviços de infraestrutura (app, nginx, postgres, redis, queue, scheduler). Os nomes dos containers, rede e volumes devem ser **únicos por projecto**.

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `# Meu Projecto — Docker Compose` | `# <Seu Projecto> — Docker Compose` |
| `container_name` dos serviços | `meu_projecto_app`, `_nginx`, `_postgres`, `_redis`, `_queue`, `_scheduler` | `<seu_projecto>_app`, `_nginx`, etc. |
| `name` da rede | `meu_projecto_network` | `<seu_projecto>_network` |
| `name` dos volumes | `meu_projecto_postgres_data`, `meu_projecto_redis_data` | `<seu_projecto>_postgres_data`, `<seu_projecto>_redis_data` |

> **Nota**: os `name:` são nomes **finais absolutos** — o Docker Compose **não** adiciona prefixo quando `name:` está definido.

---

### `docker-compose.override.yml` (dev)

Carregado **automaticamente** pelo Docker Compose. Adiciona os serviços de desenvolvimento: **node** (Vite) e **mailpit**. Também sobrescreve o nginx para usar `nginx.dev.conf` (CSP com `localhost:5173`).

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |
| `container_name` | `meu_projecto_node`, `meu_projecto_mailpit` | `<seu_projecto>_node`, `<seu_projecto>_mailpit` |

> **Produção**: para ignorar o override e subir apenas os serviços base, use:
> ```bash
> docker compose -f docker-compose.yml up -d
> ```

---

### `docker/postgres/init.sh`

Este script corre **apenas na primeira vez** que o volume do PostgreSQL é criado. Se o banco já foi inicializado, alterar este ficheiro não terá efeito (precisará apagar o volume com `docker compose down -v`).

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Timezone | `Africa/Luanda` | Timezone do seu projecto (ex: `America/Sao_Paulo`, `Europe/Lisbon`) |

> **Nota**: o script usa variáveis de ambiente (`$POSTGRES_DB`, `$POSTGRES_USER`) automaticamente — não é necessário alterar nomes de banco ou utilizador manualmente.

---

### `docker/nginx/nginx.conf` (produção)

Configuração global do Nginx para produção. CSP restrito (sem `localhost:5173`).

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |
| Rate limits (opcional) | `30r/s` API / `5r/m` login | Ajustar conforme necessidade |
| `client_max_body_size` (opcional) | `12M` | Aumentar se precisar de uploads maiores |

---

### `docker/nginx/nginx.dev.conf` (dev)

Igual ao `nginx.conf`, mas com CSP que permite `http://localhost:5173` (Vite dev server). Carregado automaticamente pelo `docker-compose.override.yml`.

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |

> **Nota**: ao alterar o `nginx.conf`, replique as mesmas alterações no `nginx.dev.conf` (excepto o CSP).

---

### `docker/nginx/default.conf`

Server block do Nginx. Os nomes dos ficheiros de log devem ser únicos para facilitar o debug quando tiver múltiplos projectos.

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |
| `access_log` | `meu_projecto_access.log` | `<seu_projecto>_access.log` |
| `error_log` | `meu_projecto_error.log` | `<seu_projecto>_error.log` |

---

### `docker/php/Dockerfile`

O Dockerfile usa um **multi-stage build** e cria um utilizador não-root por segurança. O nome do utilizador é cosmético — o importante é manter o UID/GID `1000`.

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Nome do grupo/utilizador | `appuser` (UID 1000) | Nome do seu projecto ou manter `appuser` |

> **Onde aparece**: procure por `addgroup` e `adduser` no Dockerfile. O nome é usado em 4 locais: criação do grupo, criação do utilizador, `chown` das pastas, e instrução `USER`.

---

### `docker/php/php.ini`

Configurações de runtime do PHP. Os valores estão otimizados para produção (`display_errors = Off`). Para desenvolvimento local, pode activar `display_errors = On`.

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |
| `date.timezone` | `Africa/Luanda` | Timezone do seu projecto |
| `memory_limit` (opcional) | `256M` | Ajustar conforme necessidade |
| `upload_max_filesize` (opcional) | `10M` | Ajustar se precisar de uploads maiores |
| `post_max_size` (opcional) | `12M` | Deve ser ≥ `upload_max_filesize` |

---

### `docker/redis/redis.conf`

Configuração do Redis. O `appendfilename` é o nome do ficheiro de persistência AOF em disco.

| O quê | Valor no template | Alterar para |
|-------|-------------------|-------------|
| Comentário do topo | `Meu Projecto` | Nome do seu projecto |
| `appendfilename` | `meu_projecto.aof` | `<seu_projecto>.aof` |
| `maxmemory` (opcional) | `256mb` | Ajustar conforme necessidade |

---

### `src/.env` (Laravel)

Configure o `.env` do Laravel dentro da pasta `src/`. Os valores de BD e Redis devem corresponder ao `.env` da raiz:

| O quê | Alterar para |
|-------|-------------|
| `APP_NAME` | Nome do seu projecto |
| `APP_URL` | `http://localhost:<APP_PORT>` |
| `DB_DATABASE` | Mesmo valor de `POSTGRES_DB` |
| `DB_USERNAME` | Mesmo valor de `POSTGRES_USER` |
| `DB_PASSWORD` | Mesmo valor de `POSTGRES_PASSWORD` |
| `REDIS_PASSWORD` | Mesmo valor de `REDIS_PASSWORD` |

---

## Comandos Úteis

```bash
# Subir os containers
docker compose up -d

# Parar os containers
docker compose down

# Rebuild (após alterações no Dockerfile)
docker compose up -d --build

# Entrar no container da app
docker compose exec app sh

# Artisan
docker compose exec app php artisan <comando>

# Composer
docker compose exec app composer <comando>

# Logs de um serviço
docker compose logs -f app
docker compose logs -f nginx

# Verificar status dos containers
docker compose ps

# Limpar tudo (volumes incluídos) ⚠️
docker compose down -v
```

---

## Características da Infraestrutura

### PHP (Dockerfile multi-stage)
- **Stage 1 — Builder**: compila extensões (pdo_pgsql, redis, gd, zip, bcmath, pcntl, opcache)
- **Stage 2 — Production**: imagem leve apenas com runtime, sem ferramentas de compilação
- Executa como utilizador **não-root** (UID 1000)
- `entrypoint.sh` lê Docker secrets e exporta como variáveis de ambiente

### Nginx
- `server_tokens off` — esconde versão do Nginx
- Headers de segurança: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `CSP`, `Permissions-Policy`
- Rate limiting por zona: API (`30r/s`) e autenticação (`5r/m`)
- Gzip activado para assets
- Cache de 30 dias para ficheiros estáticos
- Bloqueia acesso a ficheiros ocultos (`.env`, `.git`) e sensíveis (`.sql`, `.log`)
- Apenas métodos HTTP permitidos: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`

### PostgreSQL
- Extensões pré-instaladas: `uuid-ossp`, `unaccent`, `pg_trgm`
- Buffers otimizados para containers
- Healthcheck com `pg_isready`

### Redis
- 3 databases separados: cache (db0), filas (db1), sessões (db2)
- Política de evicção: `allkeys-lru`
- Persistência AOF com `appendfsync everysec`
- Password via Docker secret (`/run/secrets/redis_password`)
- Healthcheck com `redis-cli ping`

### Queue Worker
- Processa filas via Redis com retry automático (`--tries=3`)
- Limite de 1000 jobs ou 1 hora (`--max-jobs=1000 --max-time=3600`)
- Healthcheck com `pgrep`
- Reinicia automaticamente quando o container inicia

### Scheduler
- Executa `php artisan schedule:work` para tarefas agendadas
- Healthcheck com `pgrep`
- Reinicia automaticamente quando o container inicia

### Node (Vite) *(apenas dev)*
- Definido no `docker-compose.override.yml` (carregado automaticamente em dev)
- Imagem `node:22-alpine` dedicada para compilação de assets
- Corre `npm install` apenas se `node_modules` não existir, depois inicia o Vite
- Vite acessível em `http://localhost:5173` com Hot Module Replacement (HMR)
- Porta configurável via variável `VITE_PORT`

### Mailpit *(apenas dev)*
- Definido no `docker-compose.override.yml` (carregado automaticamente em dev)
- Captura todos os emails enviados pela aplicação (nenhum email sai para a internet)
- Interface web em `http://localhost:8025` para visualizar emails
- SMTP na porta `1025` — configurado automaticamente no `.env` do Laravel
- Porta da interface configurável via variável `MAILPIT_PORT`

### Separação Dev / Produção

| Ficheiro | Carregamento | Conteúdo |
|----------|-------------|----------|
| `docker-compose.yml` | Sempre | Serviços base: app, nginx, postgres, redis, queue, scheduler |
| `docker-compose.override.yml` | Automático em dev | node (Vite), mailpit, nginx com CSP dev |
| `nginx.conf` | Produção | CSP restrito (`'self'`) |
| `nginx.dev.conf` | Dev (via override) | CSP com `http://localhost:5173` |

```bash
# Desenvolvimento (carrega yml + override automaticamente)
docker compose up -d

# Produção (ignora override — sem node, sem mailpit)
docker compose -f docker-compose.yml up -d
```
### Docker Secrets

As passwords do PostgreSQL e Redis são geridas via Docker secrets em vez de variáveis de ambiente:

| Secret | Ficheiro | Usado por |
|--------|----------|-----------|
| `db_password` | `secrets/db_password` | postgres, app, queue, scheduler |
| `redis_password` | `secrets/redis_password` | redis, app, queue, scheduler |

**Fluxo:**
1. `secrets/db_password` e `secrets/redis_password` contêm as passwords (1 por ficheiro)
2. Docker monta os ficheiros em `/run/secrets/` dentro dos containers
3. PostgreSQL lê via `POSTGRES_PASSWORD_FILE` (suporte nativo)
4. Redis lê via `cat /run/secrets/redis_password` no command
5. App/Queue/Scheduler: `entrypoint.sh` exporta os secrets como `DB_PASSWORD` e `REDIS_PASSWORD`

**Vantagens vs variáveis de ambiente:**
- Passwords não aparecem em `docker inspect`
- Não visíveis via `docker exec <container> env`
- Ficheiros com `chmod 600` (apenas o dono lê)
- Separação clara entre configuração (`.env`) e credenciais (`secrets/`)
### Recursos (deploy limits)
Cada serviço tem limites de CPU e memória definidos para evitar consumo excessivo:

| Serviço   | Ambiente | CPU max | Memória max |
|-----------|----------|---------|-------------|
| App       | base     | 1.0     | 512M        |
| Nginx     | base     | 0.5     | 128M        |
| PostgreSQL| base     | 1.0     | 512M        |
| Redis     | base     | 0.5     | 256M        |
| Queue     | base     | 0.5     | 256M        |
| Scheduler | base     | 0.5     | 256M        |
| Node      | dev      | 0.5     | 512M        |
| Mailpit   | dev      | 0.25    | 64M         |

---

## Notas

- As portas do PostgreSQL e Redis estão expostas apenas em `127.0.0.1` (não acessíveis externamente)
- O PHP está configurado para **produção** por padrão (`display_errors = Off`). Para desenvolvimento, altere no `php.ini`
- Em produção, desactive `opcache.validate_timestamps` no `php.ini` para melhor performance
- Os logs dos containers são limitados a **10MB × 3 ficheiros** cada
- O **Node** e o **Mailpit** são serviços de dev — definidos no `docker-compose.override.yml`, carregado automaticamente
- Em **produção**, use `docker compose -f docker-compose.yml up -d` para ignorar o override
- Em produção, compile os assets com `npm run build` — os ficheiros ficam em `public/build/` e são servidos pelo nginx
- O **Scheduler** executa tarefas agendadas do Laravel automaticamente
- Healthchecks configurados em todos os serviços para monitorização automática
