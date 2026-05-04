# Laravel 12 Docker Boilerplate (Modular Architecture)

Um boilerplate profissional e production-ready baseado em **Laravel 12**, **Docker**, e **arquitetura modular**. Inclui autenticação com **Passport**, permissões com **Spatie**, e uma estrutura pronta pra escalabilidade.

## 🎯 Público-Alvo

Este boilerplate é desenhado para **projectos de médio e grande porte** que precisam de:

- Escalabilidade e preparação para migração para microserviços
- Integração com apps mobile (iOS, Android) via OAuth2
- Integração com terceiros (gateways de pagamento, APIs externas, parceiros)
- Auditoria, exportação de dados e gestão de permissões desde o início

> ⚠️ **Não recomendado** para projectos simples, blogs ou MVPs sem estes requisitos. Para esses casos, considera [Laravel Breeze](https://laravel.com/docs/starter-kits).

## 🚀 Stack Tecnológico

### Infraestrutura (Docker)

| Serviço | Imagem | Porta (default) | Notas |
|---------|--------|-----------------|-------|
| **App (PHP-FPM)** | `php:8.4-fpm-alpine` | — (interna 9000) | Multi-stage build, utilizador não-root |
| **Nginx** | `nginx:1.26.3-alpine` | `APP_PORT` (8080) | Reverse proxy para PHP-FPM |
| **PostgreSQL** | `postgres:17.4-alpine` | — (interna 5432) | Healthcheck, tuning de memória |
| **Redis** | `redis:7.4.2-alpine` | `REDIS_PORT` (6379) | Cache, sessões, filas |
| **Queue Worker** | (reutiliza app) | — | Laravel Horizon (processa filas + dashboard em `/horizon`) |
| **Scheduler** | (reutiliza app) | — | `schedule:work` |
| **Node/Vite** ¹ | `node:22.14.0-alpine` | `VITE_PORT` (5173) | HMR em desenvolvimento |
| **Mailpit** ¹ | `axllent/mailpit:v1.29.7` | `MAILPIT_PORT` (8025) / `MAILPIT_SMTP_PORT` (1025) | Captura emails em dev |
| **MinIO** ¹ | `minio/minio:RELEASE.2025-09-07T16-13-09Z` | `MINIO_PORT` (9000) / `MINIO_CONSOLE_PORT` (9001) | Object storage compatível S3 (dev) |

> ¹ Apenas em desenvolvimento (`docker-compose.override.yml`).

Todas as portas externas são ligadas a `127.0.0.1` (não expostas à rede) e **resolvidas automaticamente** pelo `setup.sh` se estiverem ocupadas.

### Aplicação (Laravel 12)

| Pacote | Versão | Função |
|--------|--------|--------|
| **Laravel Passport** | 13 | OAuth 2.0 (API tokens) |
| **Laravel Fortify** | 1.36 | Autenticação (Login, Register, 2FA) + Blade views |
| **Spatie Permission** | 7.2.4 | RBAC (Roles & Permissions) |
| **Spatie Media Library** | 11.21 | Upload e gestão de ficheiros (avatars, attachments) |
| **Spatie Activity Log** | 5.0 | Auditoria de acções (criação, edição, remoção) |
| **Maatwebsite Excel** | 3.1 | Exportação CSV e XLSX |
| **Spatie Browsershot** | 5.2 | Exportação PDF via Chromium headless |
| **dedoc/scramble** | 0.13 | Documentação OpenAPI 3.1 auto-gerada a partir do código |

## 📁 Arquitetura Modular

O projeto usa uma arquitetura **module-first**, cada módulo é autocontido:

```
src/
├── modules/
│   ├── Identity/          # Identidade — autenticação, credenciais, 2FA, tokens OAuth e Fortify
│   ├── User/              # Gestão de utilizadores — "como giro os utilizadores?" (CRUD, perfil, avatar, export)
│   ├── Authorization/     # Autorização — RBAC (Roles & Permissions → Spatie)
│   ├── Notification/      # Notificações + hub cross-module (Events/Listeners)
│   ├── ActivityLog/       # Auditoria de acções (activity log)
│   └── Export/            # Exportação CSV, XLSX e PDF (sync/async híbrido)
├── routes/                # Rotas globais
├── config/                # Configurações
└── database/              # Migrations & Seeders
```

> **Nota:** Estes 6 módulos são o **esqueleto base** (infraestrutura). Os teus módulos de negócio ficam ao lado destes — ex: `modules/Product/`, `modules/Order/`, etc.

**Cada módulo tem:**
- `Models/` — Entidades Eloquent
- `Http/Controllers/` — Controllers (dual response: JSON + Blade)
- `Http/Requests/` — Form Requests (validação)
- `Http/Resources/` — API Resources (transformação)
- `Services/` — Lógica de negócio
- `Events/` — Domain events (ex: `UserCreated`, `RoleDeleted`)
- `Listeners/` — Event listeners (log, notificação, etc.)
- `Policies/` — Autorização (`Gate::authorize()` nos controllers)
- `Providers/` — ServiceProvider do módulo (routes, policies, events)
- `Routes/` — API + Web routes
- `Database/` — Migrations & Seeders
- `Jobs/` — Jobs de background (`ShouldQueue`, retry, failure handling)
- `Tests/Api/` — Testes de endpoints API (Passport)
- `Resources/views/` — Blade templates (quando aplicável)
- `Tests/Web/` — Testes de endpoints Web (Session + Blade views)

> **Nota:** `Actions/` existe apenas no módulo Identity (requisito Fortify). Os demais módulos colocam a lógica de negócio em `Services/`.

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
- ✅ Criar `.env` e `src/.env` com passwords aleatórias (PG, Redis, MinIO)
- ✅ Resolver portas em conflito (auto-reassign), incluindo MinIO
- ✅ Construir imagem multi-stage (PHP + Vite assets em prod)
- ✅ Instalar dependências (Composer)
- ✅ Gerar APP_KEY, chaves Passport e Personal Access Client
- ✅ Rodar migrations
- ✅ Criar symlink do storage
- ✅ Criar bucket MinIO automaticamente em desenvolvimento
- ✅ Cache warming em produção (`config:cache`, `route:cache`, `view:cache`)

> O setup impede execução concorrente (lock file) e limpa containers parciais em caso de Ctrl+C.

3. **Acesse:**
- **App**: `http://localhost:<APP_PORT>` (default 8080)
- **Mailpit** (emails): `http://localhost:<MAILPIT_PORT>` (default 8025) — só dev
- **Vite HMR**: `http://localhost:<VITE_PORT>` (default 5173) — só dev
- **MinIO Console**: `http://localhost:<MINIO_CONSOLE_PORT>` (default 9001) — só dev

## 📚 Credenciais Padrão (após seed)

| Email | Senha | Role |
|-------|-------|------|
| `admin@example.com` | `password` | admin (15 permissions) |
| `user@example.com` | `password` | user (2 permissions) |

> ⚠️ **Produção:** O `setup.sh --prod` **não executa seed** — estas credenciais padrão nunca existem em produção. Se precisares de dados iniciais, cria um seeder dedicado com passwords seguras.

## 🔑 Autenticação

> **Porquê Passport e não Sanctum?** O boilerplate usa **Laravel Passport** (OAuth 2.0 completo) porque é desenhado para ecossistemas que incluem apps mobile, integrações com terceiros (gateways de pagamento, parceiros) e fluxos OAuth2 (Authorization Code, Client Credentials). Sanctum é suficiente para SPAs no mesmo domínio, mas não cobre estes casos de uso.

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

### Web (Session-based + Blade Views)

A autenticação web é handleada pelo **Fortify** com views Blade completas. Rotas web retornam **Blade views** para browsers e **JSON** para requisições API (`Accept: application/json`).

**Views de autenticação disponíveis:**
- `GET /auth/login` — Formulário de login
- `GET /auth/register` — Formulário de registo
- `GET /auth/forgot-password` — Solicitar reset de password
- `GET /auth/reset-password/{token}` — Formulário de nova password

**Dual Response Pattern:**

Todos os controllers dos módulos base suportam ambos os formatos. A decisão é automática:

```php
// No controller — retorna JSON ou Blade conforme o request
if (request()->expectsJson()) {
    return response()->json(UserResource::collection($users));
}
return view('user::users.index', compact('users'));
```

- **Browser** (session): Recebe Blade views com layout, formulários e flash messages
- **API** (`Authorization: Bearer {token}` + `Accept: application/json`): Recebe JSON

## 🔐 Permissões & Roles

### Roles Padrão

| Role | Permissions | Uso |
|------|-------------|-----|
| **admin** | Todas (17) | Acesso total |
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

**Log:**
- `log.list` — Listar activity log (admin)
- `log.view` — Ver entrada do activity log (admin ou próprio utilizador)

**Export:**
- `export.create` — Iniciar exportação (CSV, XLSX, PDF)

### Verificar Permissão em Código

```php
// In Controller (padrão do projecto — usa Gate + Policy)
Gate::authorize('viewAny', User::class);
Gate::authorize('update', $user);

// In Policy
public function create(User $user): bool {
    return $user->hasPermissionTo('user.create');
}

// Directo (alternativa)
if ($user->hasPermissionTo('user.create')) {
    // Fazer algo
}
```

## 🖼️ Upload de Ficheiros (Media Library)

O projecto usa a **Spatie Media Library** para gestão de ficheiros, com suporte a múltiplos discos de armazenamento.

### Discos de Armazenamento

| Disco | Ambiente | Serviço | Configuração |
|-------|----------|---------|-------------|
| `minio` | Desenvolvimento | MinIO (local) | `MINIO_*` em `src/.env` |
| `r2` | Produção | Cloudflare R2 | `R2_*` em `src/.env` |

O disco activo é controlado pela variável `MEDIA_DISK` (default: `minio`).

### Avatar de Utilizador

O modelo `User` já implementa `HasMedia` com uma coleção `avatar` configurada:

```bash
# Upload de avatar (multipart/form-data, campo: avatar)
# Formatos aceites: jpeg, png, webp, gif — máx. 2MB
POST /api/users/{id}/avatar

# Remover avatar
DELETE /api/users/{id}/avatar
```

O campo `avatar_url` é exposto automaticamente no `UserResource`.

### Adicionar Media a um Módulo

```php
// 1. Implementar HasMedia no Model
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia {
    use InteractsWithMedia;

    public function registerMediaCollections(): void {
        $this->addMediaCollection('thumbnail')->singleFile();
    }
}

// 2. Upload no Controller
$post->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');

// 3. Obter URL
$post->getFirstMediaUrl('thumbnail');
```

### MinIO Console (Dev)

O MinIO disponibiliza uma interface web em `http://localhost:9001` (credenciais: `MINIO_ACCESS_KEY` / `MINIO_SECRET_KEY` do `.env`).

O `setup.sh` cria o bucket automaticamente ao subir o ambiente de desenvolvimento.

## 🪵 Activity Log

O projecto usa a **Spatie Activity Log** para auditar acções nos modelos críticos.

### Modelos com Logging Automático

| Modelo | Campos logados | Eventos |
|--------|---------------|--------|
| `User` | `name`, `email` | `created`, `updated`, `deleted` |
| `Role` | `name`, `guard_name` | `created`, `updated`, `deleted` |
| `Permission` | `name`, `guard_name` | `created`, `updated`, `deleted` |
| `Notification` | `read_at` | `updated`, `deleted` |

> Campos sensíveis (`password`, tokens, timestamps) são explicitamente excluídos. Registos com dirty set vazio não são gravados (`dontLogEmptyChanges`).

> `Notification` não loga `created` — a criação da notificação é uma consequência de outro evento que já está logado (ex: `UserCreated`).

### Endpoints

```bash
# Listar activity log (requer log.list)
GET /api/v1/activity-log

# Filtros disponíveis via query string:
?causer_id=1&subject_type=User&log_name=default&date_from=2026-01-01&date_to=2026-12-31&per_page=15

# Ver entrada individual (requer log.view ou ser o causer)
GET /api/v1/activity-log/{id}

# Ver actividade de um utilizador específico (próprio utilizador ou log.list)
GET /api/v1/users/{id}/activity
```

### Log Manual

```php
// Em qualquer Service/Controller
activity()
    ->causedBy($user)
    ->performedOn($order)
    ->withProperties(['total' => 99.90])
    ->log('order.created');
```

### Acesso via RBAC

| Acção | Permissão necessária |
|-------|---------------------|
| Listar todo o log | `log.list` |
| Ver entrada específica | `log.view` OU ser o causer |
| Ver actividade própria | nenhuma (autenticado) |
| Ver actividade de outro utilizador | `log.list` |

## 🔒 Events & Notifications (Cross-Module)

O projecto usa uma **arquitetura event-driven**. Cada módulo despacha domain events, e o módulo **Notification** reage automaticamente para notificar admins.

### Events por Módulo

| Módulo | Events (classes) |
|--------|--------|
| **Identity** | eventos de autenticação/credenciais, quando existirem |
| **User** | `UserCreated`, `UserUpdated`, `UserDeleted` |
| **Authorization** | `PermissionCreated`, `PermissionUpdated`, `PermissionDeleted`, `RoleCreated`, `RoleUpdated`, `RoleDeleted`, `RoleAssigned` |
| **Notification** | `NotificationRead`, `NotificationDeleted` |

> **Nota:** `UserService` despacha `UserCreated` (que vive em `User/Events/`) e `RoleAssigned` (que vive em `Authorization/Events/`). A tabela mostra onde cada event class é definida.

### Fluxo

```
Service → dispatch(Event) → Listeners no mesmo módulo (log)
                           → Listeners no módulo Notification (notifica admins)
```

**Exemplo concreto:**
1. `UserService::create()` → `UserCreated::dispatch($user)`
2. `LogUserCreation` (Identity module) → registra no log
3. `NotifyOnUserCreated` (Notification module) → cria `ActivityNotification` para todos os admins

### Onde os Events são Registados

Cada módulo regista os seus eventos no próprio **ServiceProvider** via `Event::listen()` — não existe um `EventServiceProvider` centralizado.

```php
// Ex: Modules\Identity\Providers\IdentityServiceProvider::boot()
Event::listen(UserCreated::class, LogUserCreation::class);
```

O módulo Notification regista listeners cross-module no `NotificationServiceProvider`:
```php
Event::listen(UserCreated::class, NotifyOnUserCreated::class);
Event::listen(RoleCreated::class, NotifyOnRoleCreated::class);
// ... etc
```

### ActivityNotification

A classe `Modules\Notification\Notifications\ActivityNotification` é a notificação genérica que guarda:
- `type` — tipo da acção (ex: `user.created`, `role.deleted`)
- `message` — descrição legível
- `metadata` — dados adicionais (JSON)

As notificações são guardadas via `notify()` do Laravel (tabela `notifications`) e acessíveis nos endpoints `/api/notifications`.

## �📡 API Endpoints

### Auth (Identity)

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
| POST | `/api/users/{id}/avatar` | ✅ | próprio utilizador ou `user.update` |
| DELETE | `/api/users/{id}/avatar` | ✅ | próprio utilizador ou `user.update` |
| GET | `/api/users/{id}/activity` | ✅ | próprio utilizador ou `log.list` |

### Roles

| Método | Rota | Autenticação | Permission |
|--------|------|--------------|-----------|
| GET | `/api/roles` | ✅ | `role.list` |
| POST | `/api/roles` | ✅ | `role.create` |
| GET | `/api/roles/{id}` | ✅ | `role.view` |
| PUT | `/api/roles/{id}` | ✅ | `role.update` |
| DELETE | `/api/roles/{id}` | ✅ | `role.delete` |

### Permissions

| Método | Rota | Autenticação | Permission |
|--------|------|--------------|-----------|
| GET | `/api/permissions` | ✅ | `permission.list` |
| POST | `/api/permissions` | ✅ | `permission.create` |
| GET | `/api/permissions/{id}` | ✅ | `permission.view` |
| PUT | `/api/permissions/{id}` | ✅ | `permission.update` |
| DELETE | `/api/permissions/{id}` | ✅ | `permission.delete` |
### Activity Log

| Método | Rota | Autenticação | Permission |
|--------|------|--------------|----------|
| GET | `/api/activity-log` | ✅ | `log.list` |
| GET | `/api/activity-log/{id}` | ✅ | `log.view` ou próprio causer |

### Export

| Método | Rota | Autenticação | Descrição |
|--------|------|--------------|----------|
| POST | `/api/v1/exports` | ✅ | Iniciar exportação (sync ou async) |
| GET | `/api/v1/exports/{uuid}/status` | ✅ | Estado de exportação async |
| GET | `/api/v1/exports/{uuid}/download` | ✅ | Download de exportação async |

**Body (POST):**
```json
{
  "module": "users",
  "format": "csv",
  "filters": { "role": "admin", "search": "maria" }
}
```
- `module`: `users` \| `activity_log`
- `format`: `csv` \| `xlsx` \| `pdf`
- `filters`: opcional (filtros específicos do módulo)

**Resposta sync** (≤ `EXPORT_SYNC_LIMIT` registos): ficheiro descarregado directamente.

**Resposta async** (> `EXPORT_SYNC_LIMIT` registos): HTTP 202 com UUID. O job corre em background (Redis queue), o utilizador recebe notificação database quando pronto.
### API Documentation

| Método | Rota | Autenticação | Descrição |
|--------|------|--------------|-----------|
| GET | `/docs/api` | ❌ (apenas local) | UI interactiva Stoplight Elements |
| GET | `/docs/api.json` | ❌ (apenas local) | Especificação OpenAPI 3.1 |

> Gerado automaticamente por `dedoc/scramble` a partir dos controllers, Form Requests e API Resources. Acessível apenas em `APP_ENV=local` e `testing` (middleware `RestrictedDocsAccess`).

### Health Check

| Método | Rota | Autenticação | Descrição |
|--------|------|--------------|-----------|
| GET | `/health` | ❌ | Estado da aplicação (DB, Cache, Queue) |

**Resposta (200 — ok):**
```json
{ "status": "ok", "checks": { "database": {"status": "ok"}, "cache": {"status": "ok"}, "queue": {"status": "ok"} } }
```

**Resposta (503 — degraded):**
```json
{ "status": "degraded", "checks": { "database": {"status": "fail", "message": "Database unreachable"}, ... } }
```

> Usado por load balancers, Docker `HEALTHCHECK`, Kubernetes `livenessProbe` e monitorização externa.

### Notifications

| Método | Rota | Autenticação | Descrição |
|--------|------|--------------|-----------|
| GET | `/api/notifications` | ✅ | Listar notificações do usuário |
| GET | `/api/notifications/unread` | ✅ | Apenas não lidas |
| GET | `/api/notifications/{id}` | ✅ | Detalhe |
| PATCH | `/api/notifications/{id}/read` | ✅ | Marcar como lida |
| POST | `/api/notifications/read-all` | ✅ | Marcar todas como lidas |
| DELETE | `/api/notifications/{id}` | ✅ | Deletar notificação |

## 🪵 Rotas Web (Blade Views)

Todas as rotas web retornam Blade views para browsers (session auth via Fortify):

### Auth (Identity/Fortify)

| Método | Rota | View |
|--------|------|------|
| GET | `/auth/login` | Login form |
| POST | `/auth/login` | Processar login |
| GET | `/auth/register` | Register form |
| POST | `/auth/register` | Processar registo |
| POST | `/auth/logout` | Logout |
| GET | `/auth/forgot-password` | Forgot password form |
| POST | `/auth/forgot-password` | Enviar email reset |
| GET | `/auth/reset-password/{token}` | Reset password form |
| POST | `/auth/reset-password` | Confirmar reset |

### Users

| Método | Rota | View / Acção |
|--------|------|------|
| GET | `/users` | Lista de usuários |
| GET | `/users/create` | Formulário de criação |
| POST | `/users` | Criar usuário (redirect) |
| GET | `/users/{id}` | Detalhe do usuário |
| GET | `/users/{id}/edit` | Formulário de edição |
| PUT | `/users/{id}` | Atualizar usuário (redirect) |
| DELETE | `/users/{id}` | Deletar usuário (redirect) |

### Roles

| Método | Rota | View / Acção |
|--------|------|------|
| GET | `/roles` | Lista de roles |
| GET | `/roles/create` | Formulário de criação |
| POST | `/roles` | Criar role (redirect) |
| GET | `/roles/{id}` | Detalhe da role |
| GET | `/roles/{id}/edit` | Formulário de edição |
| PUT | `/roles/{id}` | Atualizar role (redirect) |
| DELETE | `/roles/{id}` | Deletar role (redirect) |

### Permissions

| Método | Rota | View / Acção |
|--------|------|------|
| GET | `/permissions` | Lista de permissions |
| GET | `/permissions/create` | Formulário de criação |
| POST | `/permissions` | Criar permission (redirect) |
| GET | `/permissions/{id}` | Detalhe da permission |
| GET | `/permissions/{id}/edit` | Formulário de edição |
| PUT | `/permissions/{id}` | Atualizar permission (redirect) |
| DELETE | `/permissions/{id}` | Deletar permission (redirect) |

### Notifications

| Método | Rota | View / Acção |
|--------|------|------|
| GET | `/notifications` | Lista de notificações |
| GET | `/notifications/unread` | Apenas não lidas |
| GET | `/notifications/{id}` | Detalhe da notificação |
| PATCH | `/notifications/{id}/read` | Marcar como lida (redirect) |
| POST | `/notifications/read-all` | Marcar todas como lidas (redirect) |
| DELETE | `/notifications/{id}` | Deletar notificação (redirect) |

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
- `Identity-Web` — Auth via web/session
- `Identity-Api` — Auth via API/Passport
- `User-Web` — CRUD usuários (web)
- `User-Api` — CRUD usuários (API)
- `Authorization-Web` — CRUD roles/permissions (web)
- `Authorization-Api` — CRUD roles/permissions (API)
- `Notification-Web` — Notificações (web)
- `Notification-Api` — Notificações (API)
- `ActivityLog-Api` — Activity log (API)
- `Export-Api` — Exportação CSV, XLSX e PDF (21 cenários)
- `Feature` — Inclui `HealthTest` (6 cenários) e `MakeModuleCommandTest` (46 cenários)

Rodar suite específica:
```bash
make artisan CMD="test --testsuite=User-Api"
make artisan CMD="test --testsuite=Authorization-Web"
```

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
3. **UserSeeder** — 2 usuários (admin@example.com + user@example.com) com roles atribuídas
4. **DatabaseSeeder** — orquestra a ordem: PermissionSeeder → RoleSeeder → UserSeeder

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
    │   ├── Identity/
    │   ├── User/
    │   ├── Authorization/
    │   ├── Notification/
    │   ├── ActivityLog/
    │   └── Export/
    ├── public/
    ├── resources/
    ├── routes/
    ├── storage/
    └── tests/
```

### Criar Novo Módulo

O boilerplate inclui comandos Artisan para gerar e remover módulos completos:

```bash
# Gerar módulo com toda a estrutura (Controller, Service, Model, Routes, Tests, etc.)
make artisan CMD="make:module Product"

# Gerar módulo com Blade views (dual response: JSON + Blade)
make artisan CMD="make:module Product --with-views"

# Remover módulo (apaga diretório + limpa providers.php + remove suites phpunit.xml)
make artisan CMD="remove:module Product"
```

O `make:module` cria automaticamente:
- `Http/Controllers/`, `Http/Requests/`, `Http/Resources/`
- `Models/`, `Services/`, `Policies/`
- `Jobs/` — `Process{Module}Job.php` (stub com `ShouldQueue`, `tries = 3`, `timeout = 60`, `handle()` e `failed()`)
- `Events/`, `Listeners/`
- `Providers/` (ServiceProvider com routes, policies e events)
- `Routes/` (api.php + web.php)
- `Database/Migrations/`, `Database/Seeders/`
- `Tests/Api/`, `Tests/Web/` (stubs com testes iniciais)
- Regista automaticamente o provider em `bootstrap/providers.php`
- Cria test suites `{Module}-Api` e `{Module}-Web` em `phpunit.xml`

Com `--with-views`, adiciona também:
- `Resources/views/{module}/` — 4 Blade views (index, show, create, edit) com layout base
- Controller com **dual response** (`expectsJson()` → JSON ou Blade)
- `loadViewsFrom` registado automaticamente no ServiceProvider
- Rotas web `create` e `edit` adicionais

### Conventions

- **Controllers**: `DatumController` (singular), methods: `index`, `store`, `show`, `update`, `destroy`
- **Requests**: `StoreDatumRequest`, `UpdateDatumRequest`
- **Resources**: `DatumResource::collection()` para lista
- **Services**: Lógica de negócio, sem dependências HTTP. Despacham domain events
- **Policies**: Autorização via `Gate::authorize('action', $model)` nos controllers
- **Events**: Um event por acção de negócio (ex: `UserCreated`, `RoleDeleted`)
- **Listeners**: Um listener por reacção (ex: `LogUserCreation`, `NotifyOnUserCreated`)
- **Guards**: `resolveGuardName()` nos Services — detecta `api` vs `web` dinamicamente
- **Views**: Blade templates em `Resources/views/`, registados via `loadViewsFrom` no ServiceProvider
- **Dual Response**: Controllers usam `request()->expectsJson()` para decidir JSON vs Blade
- **Models**: Use traits `HasFactory`, `Notifiable` quando precisar

### Exemplo: Criar CRUD Rápido

```bash
# 1. Gerar módulo completo (controller, service, model, routes, tests, etc.)
make artisan CMD="make:module Post"

# 2. Criar migration
make artisan CMD="make:migration create_posts_table --path=modules/Post/Database/Migrations"

# 3. Editar migration, Model, Service, Controller, Requests, Resource
# 4. Editar Policy (já criada pelo make:module)
# 5. Adicionar permissions no seeder
# 6. Escrever testes em Tests/Api/ e Tests/Web/
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

# MinIO (dev only)
MINIO_PORT=9000
MINIO_CONSOLE_PORT=9001
MINIO_ACCESS_KEY=minio
MINIO_SECRET_KEY=...         # Gerado automaticamente

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

# Upload (Media Library)
MEDIA_DISK=minio             # minio em dev, r2 em produção

# Export
EXPORT_SYNC_LIMIT=5000       # Exportações com ≤ N registos são síncronas
EXPORT_EXPIRATION_HOURS=24   # Horas até o ficheiro async expirar

# MinIO (dev)
MINIO_ACCESS_KEY=...         # Sincronizado com MINIO_ACCESS_KEY
MINIO_SECRET_KEY=...         # Sincronizado com MINIO_SECRET_KEY
MINIO_BUCKET=local
MINIO_ENDPOINT=http://minio:9000
MINIO_URL=http://localhost:9000/local  # URL pública (auto-ajustada com MINIO_PORT)

# Cloudflare R2 (produção)
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_URL=
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
| MinIO (dev) | 0.5 | 256MB | 64MB |

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
# APP_PORT, REDIS_PORT, VITE_PORT, MAILPIT_PORT, MAILPIT_SMTP_PORT, MINIO_PORT, MINIO_CONSOLE_PORT
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

## � Exportação (CSV / XLSX / PDF)

O módulo Export oferece exportação híbrida sync/async para qualquer módulo do projecto.

### Lógica Híbrida

| Condição | Comportamento |
|----------|---------------|
| `count(registos) ≤ EXPORT_SYNC_LIMIT` | Ficheiro gerado no momento (HTTP 200 download) |
| `count(registos) > EXPORT_SYNC_LIMIT` | Job despachado para fila Redis (HTTP 202 + UUID) |

### Formatos Suportados

| Formato | Biblioteca | Características |
|---------|------------|-----------------|
| **CSV** | `maatwebsite/excel` | UTF-8, BOM configurável via `config/excel.php` |
| **XLSX** | `maatwebsite/excel` | Cabeçalhos coloridos, auto-resize de colunas |
| **PDF** | `spatie/browsershot` | Gerado via Chromium headless, layout HTML/CSS completo |

### Módulos Exportáveis

| Módulo | Filtros disponíveis | Campos exportados |
|--------|--------------------|-----------------|
| `users` | `role`, `search` | ID, Nome, Email, Roles, Verificado, Criado em |
| `activity_log` | `causer_id`, `log_name`, `subject_type`, `date_from`, `date_to` | ID, Log, Descrição, Utilizador, Modelo, ID Modelo, Data |

### Exportação Assíncrona — Ciclo de Vida

```
POST /api/v1/exports  →  202 Accepted + { uuid, status: "pending" }
        ↓
  ProcessExportJob (Redis queue)
        ↓
  Export gravado em storage/app/exports/{uuid}/
        ↓
  ExportReadyNotification (database notification → utilizador)
        ↓
GET /api/v1/exports/{uuid}/status  →  { status: "completed", expires_at }
GET /api/v1/exports/{uuid}/download  →  ficheiro
```

### Expiração e Limpeza

Ficheiros expiram ao fim de `EXPORT_EXPIRATION_HOURS` horas (default: 24h).
O comando `exports:purge` é agendado diariamente e apaga ficheiros e registos expirados.

### Adicionar Suporte de Export a um Módulo

```php
// 1. Implementar ExportableInterface no service do módulo
class ProductExportService implements ExportableInterface {
    public function getQuery(array $filters = []): Builder { ... }
    public function getExportClass(array $filters = []): FromQuery { return new ProductsExport($filters); }
    public function getPdfView(): string { return 'product::exports.pdf'; }
    public function getFilename(): string { return 'products'; }
    public function getPdfData(array $filters = []): array { ... }
}

// 2. Registar em ExportController::EXPORTERS
private const EXPORTERS = [
    'users'        => UserExportService::class,
    'activity_log' => ActivityLogExportService::class,
    'products'     => ProductExportService::class, // novo
];
```

## 📚 Recursos

- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Spatie Media Library Docs](https://spatie.be/docs/laravel-medialibrary)
- [Spatie Activity Log Docs](https://spatie.be/docs/laravel-activitylog)
- [Laravel Passport Docs](https://laravel.com/docs/passport)
- [Laravel Fortify Docs](https://laravel.com/docs/fortify)
- [Maatwebsite Excel Docs](https://docs.laravel-excel.com)
- [Spatie Browsershot Docs](https://spatie.be/docs/browsershot)
- [dedoc/scramble Docs](https://scramble.dedoc.co)
- [Vite Documentation](https://vitejs.dev)
- [MinIO Documentation](https://min.io/docs/minio/container/index.html)
