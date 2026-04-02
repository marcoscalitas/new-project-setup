# Laravel 12 Docker Boilerplate (Modular Architecture)

Um boilerplate profissional e production-ready baseado em **Laravel 12**, **Docker**, e **arquitetura modular**. Inclui autenticação com **Passport**, permissões com **Spatie**, e uma estrutura pronta pra escalabilidade.

## 🚀 Stack Tecnológico

| Service    | Image                 | Port |
|------------|-----------------------|------|
| **Laravel Passport** | 13 | OAuth 2.0 |
| **Laravel Fortify** | 1.36 | Autenticação (2FA) |
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

### Instalação

1. **Clone o repositório:**
```bash
git clone <repo-url> new-project-setup
cd new-project-setup
```

2. **Execute o setup (automático):**
```bash
./setup.sh          # Desenvolvimento
./setup.sh --prod   # Produção
```

O script fará:
- ✅ Criar `.env` com valores aleatórios
- ✅ Gerar chaves OAuth do Passport
- ✅ Rodar migrations
- ✅ Fazer seed de roles/permissions/usuários

3. **Inicie os containers:**
```bash
docker compose up -d
```

4. **Acesse:**
- **API**: http://localhost:8000/api
- **Web**: http://localhost:8000
- **Mailpit** (emails): http://localhost:1025

## 📚 Credenciais Padrão (após seed)

| Email | Senha | Role |
|-------|-------|------|
| `admin@example.com` | `password` | admin (15 permissions) |
| `user@example.com` | `password` | user (2 permissions) |

## 🔑 Autenticação

### API (OAuth 2.0 - Passport)

**Login:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
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
curl -H "Authorization: Bearer {token}" http://localhost:8000/api/users
```

### Web (Session-based)

A autenticação web é handleada pelo **Fortify** (forms HTML). Rotas web exigem middleware `auth` (session).

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

**153 testes passando** (API + Web + Authentication):

```bash
# Rodar todos os testes
make test

# Ou via Docker
docker compose exec app php artisan test

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
make setup              # Setup dev
make setup-prod         # Setup produção
make up                 # Subir containers
make down               # Derrubar containers
make restart            # Reiniciar
make logs               # Ver logs (PHP)
make logs-nginx         # Ver logs (Nginx)
make shell              # Interactive shell
make migrate            # Rodar migrations
make migrate-fresh      # Resetar + migrate + seed
make seed               # Apenas seed
make artisan CMD=...    # Rodar artisan command
make tinker             # Laravel Tinker
make npm CMD=...        # Rodar npm
make composer CMD=...   # Rodar composer
make cache-clear        # Limpar cache
make cache-warm         # Esquentar cache (prod)
make db-dump            # Backup da base de dados
make db-restore FILE=.. # Restaurar backup
```

Exemplos:
```bash
make artisan CMD="make:migration create_posts_table"
make composer CMD="require symfony/console"
make npm CMD="run build"
```

## 🗄️ Database

### Migrations

Todas em `src/database/migrations/` e `src/modules/*/Database/Migrations/`:

```bash
# Rodar todas
docker compose exec app php artisan migrate

# Rodar fresh + seed
docker compose exec app php artisan migrate:fresh --seed

# Reverter última batch
docker compose exec app php artisan migrate:rollback
```

### Seeders

Executados com `migrate:fresh --seed`:

1. **PermissionSeeder** — 15 permissions × 2 guards (api, web)
2. **RoleSeeder** — admin (15 perms), user (2 perms) × 2 guards
3. **DatabaseSeeder** — 2 usuários (admin@, user@)

### Backup & Restore

```bash
# Criar backup (ficheiro comprimido em backups/)
make db-dump

# Restaurar a partir de um backup
make db-restore FILE=backups/mydb_20260402_143000.sql.gz
```

Os backups são guardados em `backups/` (já incluído no `.gitignore`).

Adicionar new seeder:
```bash
docker compose exec app php artisan make:seeder YourSeeder
# Editar src/database/seeders/YourSeeder.php
# Chamar em DatabaseSeeder::run()
```

## 🔧 Desenvolvimento

### Estrutura de Pastas

```
src/
├── app/
│   ├── Http/
│   │   └── Controllers/    # Controllers globais (se houver)
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   ├── app.php            # Configuração da app
│   └── providers.php      # Registro de providers
├── config/                # Configurações
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── modules/               # **Módulos de negócio**
├── public/
│   └── index.php
├── resources/
│   ├── css/
│   ├── js/
│   └── views/            # Blade templates
├── routes/
│   ├── console.php
│   └── web.php
├── storage/              # Logs, cache, uploads
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
# 1. Create model + migration
docker compose exec app php artisan make:model Post -m

# 2. Edit migration
# 3. Create factory
docker compose exec app php artisan make:factory PostFactory

# 4. Create service
# 5. Create controller
# 6. Create requests
# 7. Create resource
# 8. Create policy
docker compose exec app php artisan make:policy PostPolicy --model=Post

# 9. Register routes
# 10. Write tests
```

## 📝 Environment Variables

Ver `.env.example`:

```env
# App
APP_NAME=Laravel
APP_ENV=local              # local|staging|production
APP_DEBUG=true             # false em PROD
APP_KEY=...                # Gerado automaticamente
APP_URL=http://localhost

# Database
DB_HOST=db
DB_PORT=5432
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=...            # Gerado aleatoriamente

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Passport
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=...
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=...

# Mail (Mailpit)
MAIL_HOST=mailpit
MAIL_PORT=1025
```

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
docker build -f docker/php/Dockerfile -t myapp:latest .
```

**Multi-stage Dockerfile:**
- **Builder**: Instala deps, compila assets
- **Production**: Slim runtime, apenas o necessário

### Otimizações Production

```bash
# Cache warming
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Logs centralizados
# Configure no config/logging.php
```

## 🐛 Troubleshooting

### Ports em uso
```bash
# Verificar portas
lsof -i :8000
lsof -i :5432

# Mudar ports em docker-compose.override.yml
```

### Database connection error
```bash
# Verificar container
docker compose ps

# Logs do PostgreSQL
docker compose logs db

# Reset database
docker compose exec app php artisan migrate:fresh --seed
```

### Permission denied (file)
```bash
# Fix ownership
docker compose exec app chown -R www-data:www-data /var/www/storage
```

### Cache/Session issues
```bash
make cache-clear
docker compose exec app php artisan cache:forget key
```

## 📚 Recursos

- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Laravel Passport Docs](https://laravel.com/docs/passport)
- [Laravel Fortify Docs](https://laravel.com/docs/fortify)
- [Vite Documentation](https://vitejs.dev)

## 📄 License

MIT

## 👤 Autor

Criado como boilerplate profissional para Laravel 12.

---

**Status:** Production-ready ✅  
**Testes:** 153 passing  
**Última atualização:** Março 2026

