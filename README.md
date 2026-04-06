# Laravel 12 Docker Boilerplate (Modular Architecture)

Um boilerplate profissional e production-ready baseado em **Laravel 12**, **Docker**, e **arquitetura modular**. Inclui autenticação com **Passport**, permissões com **Spatie**, e uma estrutura pronta pra escalabilidade.

## 🚀 Stack Tecnológico

### Infraestrutura (Docker)

| Serviço | Imagem | Porta (default) | Notas |
|---------|--------|-----------------|-------|
| **App (PHP-FPM)** | `php:8.4-fpm-alpine` | — (interna 9000) | Multi-stage build, utilizador não-root |
| **Nginx** | `nginx:1-alpine` | `APP_PORT` (8080) | Reverse proxy para PHP-FPM |
| **PostgreSQL** | `postgres:17-alpine` | — (interna 5432) | Healthcheck, tuning de memória |
| **Redis** | `redis:7-alpine` | `REDIS_PORT` (6379) | Cache, sessões, filas |
| **Queue Worker** | (reutiliza app) | — | `queue:work redis` com limits |
| **Scheduler** | (reutiliza app) | — | `schedule:work` |
| **Node/Vite** ¹ | `node:22-alpine` | `VITE_PORT` (5173) | HMR em desenvolvimento |
| **Mailpit** ¹ | `axllent/mailpit` | `MAILPIT_PORT` (8025) / `MAILPIT_SMTP_PORT` (1025) | Captura emails em dev |

> ¹ Apenas em desenvolvimento (`docker-compose.override.yml`).

Todas as portas externas são ligadas a `127.0.0.1` (não expostas à rede) e **resolvidas automaticamente** pelo `setup.sh` se estiverem ocupadas.

### Aplicação (Laravel 12)

| Pacote | Versão | Função |
|--------|--------|--------|
| **Laravel Passport** | 13 | OAuth 2.0 (API tokens) |
| **Laravel Fortify** | 1.36 | Autenticação (Login, Register, 2FA) |
| **Spatie Permission** | 7.2.4 | RBAC (Roles & Permissions) |

## 📁 Arquitetura Modular

O projeto usa uma arquitetura **module-first**, cada módulo é autocontido:

```
src/
├── modules/
│   ├── Auth/              # Autenticação (Login, Register, 2FA, Fortify)
│   ├── User/              # CRUD de usuários
│   ├── Permission/        # RBAC (Roles & Permissions → Spatie)
│   └── Notification/      # Notificações (DatabaseNotification nativa)
├── routes/                # Rotas globais
├── config/                # Configurações
└── database/              # Migrations & Seeders
```

> **Nota:** Estes 4 módulos são o **esqueleto base** (infraestrutura). Os teus módulos de negócio ficam ao lado destes — ex: `modules/Product/`, `modules/Order/`, etc.

**Cada módulo tem:**
- `Models/` — Entidades Eloquent
- `Http/Controllers/` — Controllers (JSON responders)
- `Http/Requests/` — Form Requests (validação)
- `Http/Resources/` — API Resources (transformação)
- `Services/` — Lógica de negócio
- `Policies/` — Autorização
- `Routes/` — API + Web routes
- `Database/` — Migrations & Seeders
- `Tests/` — Testes (Web + API)

## 🛠️ Setup Rápido

### Pré-requisitos
- Docker & Docker Compose
- Git
- Make (opcional — para atalhos CLI via `Makefile`)

### Instalação

1. **Clone o repositório:**
```bash
git clone <repo-url> meu-projecto
cd meu-projecto
```

2. **Execute o setup (automático):**
```bash
./setup.sh          # Desenvolvimento
./setup.sh --prod   # Produção
```

O script fará automaticamente:
- ✅ Validar Docker, permissões, espaço em disco e conectividade
- ✅ Detectar nome de projecto duplicado e volumes órfãos
- ✅ Criar `.env` e `src/.env` com passwords aleatórias
- ✅ Resolver portas em conflito (auto-reassign)
- ✅ Construir imagem multi-stage (PHP + Vite assets em prod)
- ✅ Instalar dependências (Composer)
- ✅ Gerar APP_KEY, chaves Passport e Personal Access Client
- ✅ Rodar migrations
- ✅ Criar symlink do storage
- ✅ Cache warming em produção (`config:cache`, `route:cache`, `view:cache`)

> O setup impede execução concorrente (lock file) e limpa containers parciais em caso de Ctrl+C.

3. **Acesse:**
- **App**: `http://localhost:<APP_PORT>` (default 8080)
- **Mailpit** (emails): `http://localhost:<MAILPIT_PORT>` (default 8025) — só dev
- **Vite HMR**: `http://localhost:<VITE_PORT>` (default 5173) — só dev

## 📚 Credenciais Padrão (após seed)

| Email | Senha | Role |
|-------|-------|------|
| `admin@example.com` | `password` | admin (15 permissions) |
| `user@example.com` | `password` | user (2 permissions) |

> ⚠️ **Produção:** O `setup.sh --prod` **não executa seed** — estas credenciais padrão nunca existem em produção. Se precisares de dados iniciais, cria um seeder dedicado com passwords seguras.

## 🔑 Autenticação

### API (OAuth 2.0 - Passport)

**Login:**
```bash
curl -X POST http://localhost:${APP_PORT}/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Resposta:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "roles": [{"id": 1, "name": "admin"}]
  }
}
```

**Usar token em requisições:**
```bash
curl -H "Authorization: Bearer {token}" http://localhost:${APP_PORT}/api/users
```

### Web (Session-based)

A autenticação web é handleada pelo **Fortify** (API-only, sem views). Rotas web retornam JSON e exigem middleware `auth` (session). Não há templates Blade para login/register — os formulários devem ser criados no frontend (SPA ou Blade custom).

## 🔐 Permissões & Roles

### Roles Padrão

| Role | Permissions | Uso |
|------|-------------|-----|
| **admin** | Todas (15) | Acesso total |
| **user** | `user.list`, `user.view` | Apenas leitura |

### Permissions

Organizadas por domínio (3 "módulos" de negócio):

**User:**
- `user.list` — Listar usuários
- `user.view` — Ver usuário
- `user.create` — Criar usuário
- `user.update` — Editar usuário
- `user.delete` — Deletar usuário

**Role:**
- `role.list`, `role.view`, `role.create`, `role.update`, `role.delete`

**Permission:**
- `permission.list`, `permission.view`, `permission.create`, `permission.update`, `permission.delete`

### Verificar Permissão em Código

```php
// In Controller or Service
if ($user->hasPermissionTo('user.create')) {
    // Fazer algo
}

// In Policy
public function create(User $user): bool {
    return $user->hasPermissionTo('user.create');
}
```

## 📡 API Endpoints

### Auth

| Método | Rota | Autenticação | Descrição |
|--------|------|--------------|-----------|
| POST | `/api/auth/login` | ❌ | Login |
| POST | `/api/auth/register` | ❌ | Registrar |
| POST | `/api/auth/forgot-password` | ❌ | Solicitar reset |
| POST | `/api/auth/reset-password` | ❌ | Confirmar reset |
| POST | `/api/auth/logout` | ✅ Passport | Logout |

### Users

| Método | Rota | Autenticação | Permission |
|--------|------|--------------|-----------|
| GET | `/api/users` | ✅ | `user.list` |
| POST | `/api/users` | ✅ | `user.create` |
| GET | `/api/users/{id}` | ✅ | `user.view` |
| PUT | `/api/users/{id}` | ✅ | `user.update` |
| DELETE | `/api/users/{id}` | ✅ | `user.delete` |

### Roles

| Método | Rota | Autenticação | Permission |
|--------|------|--------------|-----------|
| GET | `/api/permissions/roles` | ✅ | `role.list` |
| POST | `/api/permissions/roles` | ✅ | `role.create` |
| GET | `/api/permissions/roles/{id}` | ✅ | `role.view` |
| PUT | `/api/permissions/roles/{id}` | ✅ | `role.update` |
| DELETE | `/api/permissions/roles/{id}` | ✅ | `role.delete` |

### Permissions

| Método | Rota | Autenticação | Permission |
|--------|------|--------------|-----------|
| GET | `/api/permissions/permissions` | ✅ | `permission.list` |
| POST | `/api/permissions/permissions` | ✅ | `permission.create` |
| GET | `/api/permissions/permissions/{id}` | ✅ | `permission.view` |
| PUT | `/api/permissions/permissions/{id}` | ✅ | `permission.update` |
| DELETE | `/api/permissions/permissions/{id}` | ✅ | `permission.delete` |

### Notifications

| Método | Rota | Autenticação | Descrição |
|--------|------|--------------|-----------|
| GET | `/api/notifications` | ✅ | Listar notificações do usuário |
| GET | `/api/notifications/unread` | ✅ | Apenas não lidas |
| GET | `/api/notifications/{id}` | ✅ | Detalhe |
| PATCH | `/api/notifications/{id}/read` | ✅ | Marcar como lida |
| POST | `/api/notifications/read-all` | ✅ | Marcar todas como lidas |
| DELETE | `/api/notifications/{id}` | ✅ | Deletar notificação |

## 🪵 Rotas Web

As mesmas rotas API estão disponíveis como web routes (session auth):

- `/users` — CRUD usuários
- `/permissions/roles` — CRUD roles
- `/permissions/permissions` — CRUD permissions
- `/notifications` — Notificações

Exemplo:
```bash
# Login via formulário
POST /login (Fortify)

# Acessar via session
GET /users (Middleware: auth)
```

## 🧪 Testes

```bash
# Rodar todos os testes
make test

# Apenas unitários
make test-unit

# Apenas feature
make test-feature

# Com cobertura
docker compose exec app php artisan test --coverage
```

**Suites:**
- `Unit` — Testes unitários (tests/Unit/)
- `Feature` — Testes de feature (tests/Feature/)
- `Auth-Web` — Auth via web/session
- `Auth-Api` — Auth via API/Passport
- `User` — CRUD usuários
- `Permission` — CRUD roles/permissions
- `Notification` — Notificações

## 📦 Makefile (Atalhos)

```bash
make help               # Mostrar todos os comandos
make setup              # Setup dev
make setup-prod         # Setup produção
make up                 # Subir containers
make down               # Derrubar containers
make restart            # Reiniciar
make build              # Rebuild imagem app
make ps                 # Estado dos containers
make logs               # Logs PHP (follow)
make logs-nginx         # Logs Nginx (follow)
make logs-queue         # Logs Queue (follow)
make shell              # Shell interactivo no container
make migrate            # Rodar migrations
make migrate-fresh      # Drop all + migrate ⚠️
make seed               # Apenas seed
make artisan CMD=...    # Rodar artisan command
make tinker             # Laravel Tinker
make npm CMD=...        # Rodar npm (container node)
make composer CMD=...   # Rodar composer
make cache-clear        # Limpar cache (config, route, view)
make cache-warm         # Esquentar cache (prod)
make db-dump            # Backup da base de dados (gzip)
make db-dump FILE=...   # Backup com nome customizado
make db-restore FILE=.. # Restaurar backup
make test               # Rodar todos os testes
make test-unit          # Testes unitários
make test-feature       # Testes de feature
make reset              # Destruir containers, volumes, rede e .env ⚠️
```

Exemplos:
```bash
make artisan CMD="make:migration create_posts_table"
make composer CMD="require symfony/console"
make npm CMD="run build"
```

## 🗄️ Database

### Migrations

As migrations ficam em `src/database/migrations/` e `src/modules/*/Database/Migrations/`:

```bash
make migrate                       # Rodar todas
make migrate-fresh                 # Drop all + migrate ⚠️
make artisan CMD="migrate:rollback"  # Reverter última batch
```

### Seeders

Executados com `migrate:fresh --seed`:

1. **PermissionSeeder** — 15 permissions × 2 guards (api, web)
2. **RoleSeeder** — admin (15 perms), user (2 perms) × 2 guards
3. **DatabaseSeeder** — 2 usuários (admin@, user@)

### Backup & Restore

```bash
# Criar backup (nome automático em backups/)
make db-dump

# Backup com nome customizado
make db-dump FILE=backups/pre-deploy.sql.gz

# Restaurar a partir de um backup
make db-restore FILE=backups/mydb_20260402_143000.sql.gz
```

Os backups são guardados em `backups/` (já incluído no `.gitignore`).

Adicionar novo seeder:
```bash
make artisan CMD="make:seeder YourSeeder"
# Editar src/database/seeders/YourSeeder.php
# Chamar em DatabaseSeeder::run()
```

## 🔧 Segurança

- Containers correm com **utilizador não-root** (`appuser`, UID/GID do host)
- Ficheiros `.env` são criados com `chmod 600`
- PostgreSQL não expõe porta externamente (apenas rede interna Docker)
- Todas as portas externas ligam a `127.0.0.1`
- Nginx inclui headers **CSP**, **X-Content-Type-Options**, **X-Frame-Options**
- Redis requer password (`REDIS_PASSWORD`)
- Queue worker com limites: `--memory=128 --max-time=3600 --max-jobs=1000`
- Logs rotativos em todos os containers (`max-size: 10m`, `max-file: 3`)

## 🔧 Desenvolvimento

### Estrutura de Pastas

```
├── docker-compose.yml           # Serviços base (prod + dev)
├── docker-compose.override.yml  # Override dev (node, mailpit)
├── Makefile                     # Atalhos CLI
├── setup.sh                     # Setup automatizado
├── .env.example                 # Template Docker env
├── docker/
│   ├── php/
│   │   ├── Dockerfile           # Multi-stage (4 stages)
│   │   ├── php.local.ini        # Config PHP dev
│   │   └── php.production.ini   # Config PHP prod
│   ├── nginx/
│   │   ├── nginx.conf           # Nginx prod (CSP headers)
│   │   ├── nginx.dev.conf       # Nginx dev (Vite HMR CSP)
│   │   └── default.conf         # Server block
│   ├── postgres/
│   │   └── init.sh              # Extensões (uuid-ossp, etc.)
│   └── redis/
│       └── redis.conf           # Config Redis
├── backups/                     # Dumps da BD (gitignored)
└── src/                         # Código Laravel
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── modules/                 # **Módulos de negócio**
    │   ├── Auth/
    │   ├── User/
    │   ├── Permission/
    │   └── Notification/
    ├── public/
    ├── resources/
    ├── routes/
    ├── storage/
    └── tests/
```

### Criar Novo Módulo

```bash
# 1. Criar diretório
mkdir -p src/modules/YourModule/{Http/Controllers,Routes,Services,Models,Policies,Tests}

# 2. Criar ServiceProvider
cat > src/modules/YourModule/Providers/YourModuleServiceProvider.php << 'EOF'
<?php
namespace Modules\YourModule\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class YourModuleServiceProvider extends ServiceProvider {
    public function boot(): void {
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        Route::prefix('api')->middleware('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
EOF

# 3. Registrar em src/bootstrap/providers.php
# Modules\YourModule\Providers\YourModuleServiceProvider::class,
```

### Conventions

- **Controllers**: `DatumController` (singular), methods: `index`, `store`, `show`, `update`, `destroy`
- **Requests**: `StoreDatumRequest`, `UpdateDatumRequest`
- **Resources**: `DatumResource::collection()` para lista
- **Services**: Lógica de negócio, sem dependências HTTP
- **Policies**: Autorização via `$user->can()` ou middleware `authorize()`
- **Models**: Use traits `HasFactory`, `Notifiable` quando precisar

### Exemplo: Criar CRUD Rápido

```bash
# 1. Criar model + migration
make artisan CMD="make:model Post -m"

# 2. Editar migration
# 3. Criar factory
make artisan CMD="make:factory PostFactory"

# 4. Criar service, controller, requests, resource
# 5. Criar policy
make artisan CMD="make:policy PostPolicy --model=Post"

# 6. Registar routes
# 7. Escrever testes
```

## 📝 Environment Variables

O projecto usa **dois ficheiros `.env`**, ambos gerados automaticamente pelo `setup.sh`:

### `.env` (raiz — Docker)

```env
# Project
PROJECT_NAME=meu-projecto
APP_ENV=local                # local | production
APP_PORT=8080                # Porta do Nginx (configável, auto-resolved)

# PostgreSQL
POSTGRES_DB=meu_projecto_db
POSTGRES_USER=meu_projecto_user
POSTGRES_PASSWORD=...        # Gerado automaticamente

# Redis
REDIS_PORT=6379
REDIS_PASSWORD=...           # Gerado automaticamente

# Dev only
VITE_PORT=5173
MAILPIT_PORT=8025
MAILPIT_SMTP_PORT=1025
```

### `src/.env` (Laravel)

```env
APP_NAME=Laravel
APP_ENV=local                # Sincronizado com .env da raiz
APP_DEBUG=true               # false em produção
APP_KEY=...                  # Gerado via artisan key:generate
APP_URL=http://localhost:8080

DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=meu_projecto_db  # Sincronizado com POSTGRES_DB
DB_USERNAME=meu_projecto_user
DB_PASSWORD=...              # Sincronizado com POSTGRES_PASSWORD

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=...           # Sincronizado com REDIS_PASSWORD

MAIL_HOST=mailpit
MAIL_PORT=1025
```

> Ambos os ficheiros são criados com `chmod 600` e incluídos no `.gitignore`.

## 🚀 Deployment

### HTTPS / SSL

Este boilerplate **não inclui HTTPS** — o Nginx interno ouve apenas na porta 80. Isso é intencional: em produção, o SSL deve ser terminado por um reverse proxy externo:

- **Traefik** — auto-provisioning de certificados Let's Encrypt
- **Caddy** — HTTPS automático sem configuração
- **Cloudflare** — SSL na edge, sem certificado no servidor
- **Nginx externo** — com Certbot/Let's Encrypt

Após configurar HTTPS, activa o cookie seguro no `src/.env`:
```env
SESSION_SECURE_COOKIE=true
```

### Build Production

```bash
./setup.sh --prod

# Ou manualmente:
docker compose -f docker-compose.yml up -d --build
```

**Multi-stage Dockerfile** (`docker/php/Dockerfile`):

| Stage | Base | Função |
|-------|------|--------|
| **builder** | `php:8.4-fpm-alpine` | Compila extensões PHP (pdo_pgsql, redis, gd, etc.) |
| **node-builder** | `node:22-alpine` | `npm ci` + `npm run build` (Vite → assets minificados) |
| **production** | `php:8.4-fpm-alpine` | Runtime leve, utilizador não-root (`appuser`), assets copiados |
| **development** | (extends production) | Adiciona `bash` para debug |

Em desenvolvimento, o `docker-compose.override.yml` usa o target `development` e o container `node` serve assets via Vite HMR. Em produção, os assets são compilados no build e servidos estaticamente pelo Nginx.

### Otimizações Production

```bash
# Cache warming (feito automaticamente pelo setup.sh --prod)
docker compose -f docker-compose.yml exec app php artisan config:cache
docker compose -f docker-compose.yml exec app php artisan route:cache
docker compose -f docker-compose.yml exec app php artisan view:cache
```

### Resource Limits

Todos os containers têm limites de CPU e memória configurados no `docker-compose.yml`:

| Serviço | CPU max | RAM max | RAM reservada |
|---------|---------|---------|---------------|
| App | 1.0 | 512MB | 128MB |
| Nginx | 0.5 | 128MB | 32MB |
| PostgreSQL | 1.0 | 512MB | 256MB |
| Redis | 0.5 | 256MB | 64MB |
| Queue | 0.5 | 256MB | 64MB |
| Scheduler | 0.5 | 256MB | 64MB |
| Node (dev) | 0.5 | 512MB | 64MB |
| Mailpit (dev) | 0.25 | 64MB | 16MB |

### Múltiplos Projectos

Este boilerplate pode ser clonado em vários directórios sem conflitos:

- Cada projecto tem nome único (validado pelo `setup.sh`)
- Containers, volumes e rede usam o nome do projecto como prefixo
- Portas em conflito são resolvidas automaticamente
- O `setup.sh` detecta nomes duplicados via `docker compose ls`

## 🐛 Troubleshooting

### Ports em uso

O `setup.sh` resolve conflitos de portas automaticamente. Se precisares verificar manualmente:

```bash
# Verificar portas
lsof -i :8080
lsof -i :5432

# As portas são configuráveis via .env:
# APP_PORT, REDIS_PORT, VITE_PORT, MAILPIT_PORT, MAILPIT_SMTP_PORT
```

### Database connection error
```bash
# Verificar container
docker compose ps

# Logs do PostgreSQL
docker compose logs postgres

# Reset database
make migrate-fresh
```

### Permission denied (file)
```bash
# Fix ownership (container usa appuser, não www-data)
docker compose exec app chown -R appuser:appuser /var/www/storage
```

### Cache/Session issues
```bash
make cache-clear
```

## 📚 Recursos

- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Laravel Passport Docs](https://laravel.com/docs/passport)
- [Laravel Fortify Docs](https://laravel.com/docs/fortify)
- [Vite Documentation](https://vitejs.dev)

