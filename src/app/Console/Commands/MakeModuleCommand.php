<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
        {name : The module name (e.g. Product)}
        {--type=domain : Module type: domain or infrastructure}
        {--with-views : (domain only) Generate Blade views and dual-response controller}';

    protected $description = 'Generate a new module following the project pattern';

    private string $module;
    private string $modulePath;

    public function handle(): int
    {
        $this->module     = Str::studly($this->argument('name'));
        $this->modulePath = base_path("modules/{$this->module}");

        if (empty($this->module) || !preg_match('/^[A-Z][a-zA-Z0-9]*$/', $this->module)) {
            $this->error('Please provide a valid module name (letters and numbers only).');
            return self::FAILURE;
        }

        if (is_dir($this->modulePath)) {
            $this->error("Module [{$this->module}] already exists!");
            return self::FAILURE;
        }

        $type = $this->option('type');

        if (!in_array($type, ['domain', 'infrastructure'])) {
            $this->error('Invalid --type. Use: domain or infrastructure.');
            return self::FAILURE;
        }

        if ($this->isInfrastructure() && $this->option('with-views')) {
            $this->warn('--with-views is ignored for infrastructure modules.');
        }

        $this->createDirectories();
        $this->createProvider();
        $this->createModel();
        $this->createMigration();
        $this->createFactory();
        $this->createSeeder();
        $this->createEvents();
        $this->createController();
        $this->createService();
        $this->createJob();
        $this->createRequests();
        $this->createResource();
        $this->createPolicy();
        $this->createRoutes();

        if ($this->isDomain() && $this->option('with-views')) {
            $this->createViews();
        }

        $this->createTests();
        $this->registerProvider();
        $this->registerTestSuite();

        $typeLabel = $this->isInfrastructure() ? 'infrastructure' : 'domain';
        $this->components->info("Module [{$this->module}] ({$typeLabel}) created successfully.");

        $bullets = [
            "Provider registered in <comment>bootstrap/providers.php</comment>",
            "Test suite registered in <comment>phpunit.xml</comment>",
            "Events scaffolded: <comment>{$this->module}Created</comment>, <comment>{$this->module}Updated</comment>, <comment>{$this->module}Deleted</comment>",
            "Add permission seeds to <comment>modules/{$this->module}/Database/Seeders/{$this->module}Seeder.php</comment>",
            "Register cross-module listeners in <comment>modules/{$this->module}/Providers/{$this->module}ServiceProvider.php</comment>",
            "Run <comment>php artisan migrate</comment> after creating migrations",
        ];

        if ($this->isInfrastructure()) {
            $bullets[] = "Infrastructure module: API-only, no web routes or views";
            $bullets[] = "Bind your service interface in <comment>register()</comment> inside the ServiceProvider";
        }

        $this->components->bulletList($bullets);

        return self::SUCCESS;
    }

    private function isDomain(): bool
    {
        return $this->option('type') === 'domain';
    }

    private function isInfrastructure(): bool
    {
        return $this->option('type') === 'infrastructure';
    }

    private function createDirectories(): void
    {
        $slug = Str::kebab(Str::plural($this->module));

        $dirs = [
            'Database/Factories',
            'Database/Migrations',
            'Database/Seeders',
            'Events',
            'Http/Controllers/Api',
            'Http/Requests',
            'Http/Resources',
            'Jobs',
            'Listeners',
            'Mail',
            'Models',
            'Policies',
            'Providers',
            'Routes',
            'Services',
            'Tests/Api',
        ];

        $fileWillExist = [
            'Database/Factories',
            'Database/Migrations',
            'Database/Seeders',
            'Events',
            'Http/Controllers/Api',
            'Http/Requests',
            'Http/Resources',
            'Jobs',
            'Models',
            'Policies',
            'Providers',
            'Routes',
            'Services',
            'Tests/Api',
        ];

        // Domain modules also have web controllers and web tests
        if ($this->isDomain()) {
            $dirs[]          = 'Http/Controllers/Web';
            $fileWillExist[] = 'Http/Controllers/Web';
            $dirs[]          = 'Tests/Web';
            $fileWillExist[] = 'Tests/Web';
        }

        // Domain modules with views
        if ($this->isDomain() && $this->option('with-views')) {
            $dirs[]          = "Resources/views/{$slug}";
            $fileWillExist[] = "Resources/views/{$slug}";
        }

        foreach ($dirs as $dir) {
            $path = "{$this->modulePath}/{$dir}";
            mkdir($path, 0755, true);

            if (!in_array($dir, $fileWillExist)) {
                touch("{$path}/.gitkeep");
            }
        }
    }

    private function createProvider(): void
    {
        $lower     = Str::lower($this->module);
        $viewsLine = ($this->isDomain() && $this->option('with-views'))
            ? "\n        if (is_dir(\$views = __DIR__ . '/../Resources/views')) {\n            \$this->loadViewsFrom(\$views, '{$lower}');\n        }"
            : '';

        $webRouteBlock = $this->isDomain()
            ? "\n                if (file_exists(\\\$web = __DIR__ . '/../Routes/web.php')) {\n                    Route::middleware('web')->group(\\\$web);\n                }\n"
            : '';

        $this->write('Providers/' . $this->module . 'ServiceProvider.php', <<<PHP
        <?php

        namespace Modules\\{$this->module}\Providers;

        use Illuminate\Support\Facades\Event;
        use Illuminate\Support\Facades\Gate;
        use Illuminate\Support\Facades\Route;
        use Illuminate\Support\ServiceProvider;
        use Modules\\{$this->module}\Models\\{$this->module};
        use Modules\\{$this->module}\Policies\\{$this->module}Policy;

        class {$this->module}ServiceProvider extends ServiceProvider
        {
            public function register(): void
            {
                //
            }

            public function boot(): void
            {
                {$webRouteBlock}
                if (file_exists(\$api = __DIR__ . '/../Routes/api.php')) {
                    Route::prefix('api/v1')
                        ->middleware('api')
                        ->group(\$api);
                }

                if (is_dir(\$migrations = __DIR__ . '/../Database/Migrations')) {
                    \$this->loadMigrationsFrom(\$migrations);
                }{$viewsLine}

                Gate::policy({$this->module}::class, {$this->module}Policy::class);

                // Register cross-module listeners here:
                // Event::listen(SomeOtherModuleEvent::class, Handle{$this->module}Listener::class);
            }
        }
        PHP);
    }

    private function createModel(): void
    {
        $this->write("Models/{$this->module}.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Models;

        use Illuminate\Database\Eloquent\Factories\Factory;
        use Illuminate\Database\Eloquent\Model;
        use Illuminate\Database\Eloquent\SoftDeletes;
        use Shared\Traits\HasUlid;
        use Modules\\{$this->module}\Database\Factories\\{$this->module}Factory;

        class {$this->module} extends Model
        {
            use HasUlid, SoftDeletes;

            protected \$fillable = [
                //
            ];

            protected static function newFactory(): Factory
            {
                return {$this->module}Factory::new();
            }
        }
        PHP);
    }

    private function createController(): void
    {
        $lower = Str::camel($this->module);

        $this->write("Http/Controllers/Api/{$this->module}Controller.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Controllers\Api;

        use Illuminate\Http\JsonResponse;
        use Illuminate\Http\Request;
        use Illuminate\Support\Facades\Gate;
        use Modules\\{$this->module}\Http\Requests\Store{$this->module}Request;
        use Modules\\{$this->module}\Http\Requests\Update{$this->module}Request;
        use Modules\\{$this->module}\Http\Resources\\{$this->module}Resource;
        use Modules\\{$this->module}\Models\\{$this->module};
        use Modules\\{$this->module}\Services\\{$this->module}Service;

        class {$this->module}Controller
        {
            public function __construct(private {$this->module}Service \${$lower}Service)
            {
            }

            public function index(Request \$request): JsonResponse
            {
                Gate::authorize('viewAny', {$this->module}::class);

                \$perPage = min((int) \$request->query('per_page', 15), 100);
                \$items = \$this->{$lower}Service->getAll(\$perPage);

                return {$this->module}Resource::collection(\$items)->response();
            }

            public function store(Store{$this->module}Request \$request): JsonResponse
            {
                Gate::authorize('create', {$this->module}::class);

                \$item = \$this->{$lower}Service->create(\$request->validated());

                return response()->json(new {$this->module}Resource(\$item), 201);
            }

            public function show(int \$id): JsonResponse
            {
                \$item = \$this->{$lower}Service->findById(\$id);

                Gate::authorize('view', \$item);

                return response()->json(new {$this->module}Resource(\$item));
            }

            public function update(Update{$this->module}Request \$request, int \$id): JsonResponse
            {
                Gate::authorize('update', {$this->module}::findOrFail(\$id));

                \$item = \$this->{$lower}Service->update(\$id, \$request->validated());

                return response()->json(new {$this->module}Resource(\$item));
            }

            public function destroy(int \$id): JsonResponse
            {
                Gate::authorize('delete', {$this->module}::findOrFail(\$id));

                \$this->{$lower}Service->delete(\$id);

                return response()->json(null, 204);
            }
        }
        PHP);

        if ($this->isDomain() && $this->option('with-views')) {
            $this->createWebController();
        }
    }

    private function createWebController(): void
    {
        $lower     = Str::camel($this->module);
        $slug      = Str::kebab(Str::plural($this->module));
        $namespace = Str::lower($this->module);

        $this->write("Http/Controllers/Web/{$this->module}Controller.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Controllers\Web;

        use Illuminate\Http\RedirectResponse;
        use Illuminate\Support\Facades\Gate;
        use Illuminate\View\View;
        use Modules\\{$this->module}\Http\Requests\Store{$this->module}Request;
        use Modules\\{$this->module}\Http\Requests\Update{$this->module}Request;
        use Modules\\{$this->module}\Models\\{$this->module};
        use Modules\\{$this->module}\Services\\{$this->module}Service;

        class {$this->module}Controller
        {
            public function __construct(private {$this->module}Service \${$lower}Service)
            {
            }

            public function index(): View
            {
                Gate::authorize('viewAny', {$this->module}::class);

                \$items = \$this->{$lower}Service->getAll(null);

                return view('{$namespace}::{$slug}.index', compact('items'));
            }

            public function create(): View
            {
                Gate::authorize('create', {$this->module}::class);

                return view('{$namespace}::{$slug}.create');
            }

            public function store(Store{$this->module}Request \$request): RedirectResponse
            {
                Gate::authorize('create', {$this->module}::class);

                \$this->{$lower}Service->create(\$request->validated());

                return redirect()->route('{$slug}.index')->with('success', '{$this->module} created successfully.');
            }

            public function show(int \$id): View
            {
                \$item = \$this->{$lower}Service->findById(\$id);

                Gate::authorize('view', \$item);

                return view('{$namespace}::{$slug}.show', compact('item'));
            }

            public function edit(int \$id): View
            {
                \$item = {$this->module}::findOrFail(\$id);

                Gate::authorize('update', \$item);

                return view('{$namespace}::{$slug}.edit', compact('item'));
            }

            public function update(Update{$this->module}Request \$request, int \$id): RedirectResponse
            {
                Gate::authorize('update', {$this->module}::findOrFail(\$id));

                \$this->{$lower}Service->update(\$id, \$request->validated());

                return redirect()->route('{$slug}.show', \$id)->with('success', '{$this->module} updated successfully.');
            }

            public function destroy(int \$id): RedirectResponse
            {
                Gate::authorize('delete', {$this->module}::findOrFail(\$id));

                \$this->{$lower}Service->delete(\$id);

                return redirect()->route('{$slug}.index')->with('success', '{$this->module} deleted successfully.');
            }
        }
        PHP);
    }

    private function createService(): void
    {
        $this->write("Services/{$this->module}Service.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Services;

        use Illuminate\Contracts\Pagination\LengthAwarePaginator;
        use Modules\\{$this->module}\Events\\{$this->module}Created;
        use Modules\\{$this->module}\Events\\{$this->module}Deleted;
        use Modules\\{$this->module}\Events\\{$this->module}Updated;
        use Modules\\{$this->module}\Models\\{$this->module};

        class {$this->module}Service
        {
            public function getAll(int \$perPage = 15): LengthAwarePaginator
            {
                return {$this->module}::paginate(\$perPage);
            }

            public function create(array \$data): {$this->module}
            {
                \$item = {$this->module}::create(\$data);

                {$this->module}Created::dispatch(\$item);

                return \$item;
            }

            public function update(int \$id, array \$data): {$this->module}
            {
                \$item = {$this->module}::findOrFail(\$id);
                \$item->update(\$data);

                {$this->module}Updated::dispatch(\$item);

                return \$item;
            }

            public function delete(int \$id): void
            {
                \$item = {$this->module}::findOrFail(\$id);
                \$item->delete();

                {$this->module}Deleted::dispatch(\$id);
            }
        }
        PHP);
    }

    private function createEvents(): void
    {
        foreach (['Created', 'Updated', 'Deleted'] as $event) {
            $property = $event === 'Deleted'
                ? "public readonly int \$id"
                : "public readonly {$this->module} \$item";

            $import = $event === 'Deleted'
                ? ''
                : "\nuse Modules\\{$this->module}\Models\\{$this->module};";

            $this->write("Events/{$this->module}{$event}.php", <<<PHP
            <?php

            namespace Modules\\{$this->module}\Events;
            {$import}
            class {$this->module}{$event}
            {
                public function __construct({$property}) {}
            }
            PHP);
        }
    }

    private function createSeeder(): void
    {
        $lower = Str::snake($this->module);

        $this->write("Database/Seeders/{$this->module}Seeder.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Database\Seeders;

        use Illuminate\Database\Seeder;
        use Modules\Authorization\Models\Permission;

        class {$this->module}Seeder extends Seeder
        {
            public function run(): void
            {
                \$permissions = [
                    '{$lower}.list',
                    '{$lower}.view',
                    '{$lower}.create',
                    '{$lower}.update',
                    '{$lower}.delete',
                ];

                foreach (\$permissions as \$permission) {
                    Permission::firstOrCreate(
                        ['name' => \$permission, 'guard_name' => 'api'],
                    );
                }
            }
        }
        PHP);
    }

    private function createRequests(): void
    {
        $this->write("Http/Requests/Store{$this->module}Request.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Requests;

        use Illuminate\Foundation\Http\FormRequest;

        class Store{$this->module}Request extends FormRequest
        {
            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [
                    //
                ];
            }
        }
        PHP);

        $this->write("Http/Requests/Update{$this->module}Request.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Requests;

        use Illuminate\Foundation\Http\FormRequest;

        class Update{$this->module}Request extends FormRequest
        {
            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [
                    //
                ];
            }
        }
        PHP);
    }

    private function createResource(): void
    {
        $this->write("Http/Resources/{$this->module}Resource.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Resources;

        use Illuminate\Http\Request;
        use Illuminate\Http\Resources\Json\JsonResource;

        class {$this->module}Resource extends JsonResource
        {
            public function toArray(Request \$request): array
            {
                return [
                    'id'         => \$this->ulid,
                    'created_at' => \$this->created_at?->toISOString(),
                    'updated_at' => \$this->updated_at?->toISOString(),
                ];
            }
        }
        PHP);
    }

    private function createPolicy(): void
    {
        $lower = Str::snake($this->module);

        $this->write("Policies/{$this->module}Policy.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Policies;

        use Modules\User\Models\User;

        class {$this->module}Policy
        {
            public function viewAny(User \$user): bool
            {
                return \$user->checkPermissionTo('{$lower}.list');
            }

            public function view(User \$user, mixed \$model): bool
            {
                return \$user->checkPermissionTo('{$lower}.view');
            }

            public function create(User \$user): bool
            {
                return \$user->checkPermissionTo('{$lower}.create');
            }

            public function update(User \$user, mixed \$model): bool
            {
                return \$user->checkPermissionTo('{$lower}.update');
            }

            public function delete(User \$user, mixed \$model): bool
            {
                return \$user->checkPermissionTo('{$lower}.delete');
            }
        }
        PHP);
    }

    private function createRoutes(): void
    {
        $slug = Str::kebab(Str::plural($this->module));

        $this->write('Routes/api.php', <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;
        use Modules\\{$this->module}\Http\Controllers\Api\\{$this->module}Controller;

        Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
            Route::apiResource('{$slug}', {$this->module}Controller::class);
        });
        PHP);

        if ($this->isDomain()) {
            if ($this->option('with-views')) {
                $this->createWebRoutesWithViews($slug);
            } else {
                $this->createWebRoutes($slug);
            }
        }
    }

    private function createWebRoutes(string $slug): void
    {
        $this->write('Routes/web.php', <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;
        use Modules\\{$this->module}\Http\Controllers\Api\\{$this->module}Controller;

        Route::prefix('{$slug}')
            ->middleware(['auth', 'throttle:60,1'])
            ->group(function () {
                Route::get('/',        [{$this->module}Controller::class, 'index'])->name('{$slug}.index');
                Route::post('/',       [{$this->module}Controller::class, 'store'])->name('{$slug}.store');
                Route::get('/{id}',    [{$this->module}Controller::class, 'show'])->name('{$slug}.show');
                Route::put('/{id}',    [{$this->module}Controller::class, 'update'])->name('{$slug}.update');
                Route::delete('/{id}', [{$this->module}Controller::class, 'destroy'])->name('{$slug}.destroy');
            });
        PHP);
    }

    private function createWebRoutesWithViews(string $slug): void
    {
        $this->write('Routes/web.php', <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;
        use Modules\\{$this->module}\Http\Controllers\Web\\{$this->module}Controller;

        Route::prefix('{$slug}')
            ->middleware(['auth', 'throttle:60,1'])
            ->group(function () {
                Route::get('/',          [{$this->module}Controller::class, 'index'])->name('{$slug}.index');
                Route::get('/create',    [{$this->module}Controller::class, 'create'])->name('{$slug}.create');
                Route::post('/',         [{$this->module}Controller::class, 'store'])->name('{$slug}.store');
                Route::get('/{id}',      [{$this->module}Controller::class, 'show'])->name('{$slug}.show');
                Route::get('/{id}/edit', [{$this->module}Controller::class, 'edit'])->name('{$slug}.edit');
                Route::put('/{id}',      [{$this->module}Controller::class, 'update'])->name('{$slug}.update');
                Route::delete('/{id}',   [{$this->module}Controller::class, 'destroy'])->name('{$slug}.destroy');
            });
        PHP);
    }

    private function createMigration(): void
    {
        $table    = Str::snake(Str::plural($this->module));
        $filename = date('Y_m_d_His') . "_create_{$table}_table.php";

        $this->write("Database/Migrations/{$filename}", <<<PHP
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('{$table}', function (Blueprint \$table) {
                    \$table->id();
                    \$table->char('ulid', 26)->unique();
                    // \$table->string('name');
                    \$table->timestamps();
                    \$table->softDeletes();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('{$table}');
            }
        };
        PHP);
    }

    private function createFactory(): void
    {
        $this->write("Database/Factories/{$this->module}Factory.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Database\Factories;

        use Illuminate\Database\Eloquent\Factories\Factory;
        use Modules\\{$this->module}\Models\\{$this->module};

        class {$this->module}Factory extends Factory
        {
            protected \$model = {$this->module}::class;

            public function definition(): array
            {
                return [
                    // 'name' => fake()->word(),
                ];
            }
        }
        PHP);
    }

    private function createJob(): void
    {
        $this->write("Jobs/Process{$this->module}Job.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\\Jobs;

        use Illuminate\\Bus\\Queueable;
        use Illuminate\\Contracts\\Queue\\ShouldQueue;
        use Illuminate\\Foundation\\Bus\\Dispatchable;
        use Illuminate\\Queue\\InteractsWithQueue;
        use Illuminate\\Queue\\SerializesModels;
        use Throwable;

        class Process{$this->module}Job implements ShouldQueue
        {
            use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

            public int \$tries = 3;
            public int \$timeout = 60;

            public function __construct(
                //
            ) {}

            public function handle(): void
            {
                //
            }

            public function failed(Throwable \$e): void
            {
                report(\$e);
            }
        }
        PHP);
    }

    private function createTests(): void
    {
        $this->write("Tests/Api/{$this->module}Test.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Tests\Api;

        use Illuminate\Foundation\Testing\RefreshDatabase;
        use Laravel\Passport\Client;
        use Modules\Authorization\Models\Permission;
        use Modules\User\Models\User;
        use Tests\TestCase;

        class {$this->module}Test extends TestCase
        {
            use RefreshDatabase;

            private User \$user;
            private string \$token;

            protected function setUp(): void
            {
                parent::setUp();

                if (!file_exists(storage_path('oauth-private.key'))) {
                    \$this->artisan('passport:keys', ['--force' => true]);
                }

                Client::create([
                    'name'          => 'Test Personal Client',
                    'secret'        => null,
                    'redirect_uris' => [],
                    'grant_types'   => ['personal_access'],
                    'provider'      => 'users',
                    'revoked'       => false,
                ]);

                \$this->user  = User::factory()->create();
                \$this->token = \$this->user->createToken('test')->accessToken;

                \$this->grantPermissions();
            }

            private function grantPermissions(): void
            {
                \$names = ['{$this->permSnake()}.list', '{$this->permSnake()}.view', '{$this->permSnake()}.create', '{$this->permSnake()}.update', '{$this->permSnake()}.delete'];
                \$perms = array_map(fn (\$n) => Permission::firstOrCreate(['name' => \$n, 'guard_name' => 'api']), \$names);
                \$this->user->givePermissionTo(\$perms);
            }

            private function authHeaders(): array
            {
                return ['Authorization' => 'Bearer ' . \$this->token];
            }

            public function test_unauthenticated_cannot_list(): void
            {
                \$this->getJson('/api/v1/{$this->slug()}')->assertUnauthorized();
            }

            public function test_user_without_permission_cannot_list(): void
            {
                \$guest = User::factory()->create();
                \$token = \$guest->createToken('test')->accessToken;

                \$this->getJson('/api/v1/{$this->slug()}', ['Authorization' => 'Bearer ' . \$token])->assertForbidden();
            }

            public function test_authenticated_user_can_list(): void
            {
                \$response = \$this->getJson('/api/v1/{$this->slug()}', \$this->authHeaders());

                \$response->assertOk()->assertJsonStructure(['data', 'links', 'meta']);
            }

            public function test_unauthenticated_cannot_create(): void
            {
                \$this->postJson('/api/v1/{$this->slug()}', [])->assertUnauthorized();
            }

            public function test_user_without_permission_cannot_create(): void
            {
                \$guest = User::factory()->create();
                \$token = \$guest->createToken('test')->accessToken;

                \$this->postJson('/api/v1/{$this->slug()}', [], ['Authorization' => 'Bearer ' . \$token])->assertForbidden();
            }

            public function test_show_returns_404_for_nonexistent(): void
            {
                \$this->getJson('/api/v1/{$this->slug()}/999', \$this->authHeaders())->assertNotFound();
            }

            public function test_destroy_returns_404_for_nonexistent(): void
            {
                \$this->deleteJson('/api/v1/{$this->slug()}/999', [], \$this->authHeaders())->assertNotFound();
            }

            public function test_user_without_permission_cannot_delete(): void
            {
                \$guest = User::factory()->create();
                \$token = \$guest->createToken('test')->accessToken;

                \$this->deleteJson('/api/v1/{$this->slug()}/1', [], ['Authorization' => 'Bearer ' . \$token])->assertForbidden();
            }
        }
        PHP);

        if ($this->isDomain()) {
            $this->write("Tests/Web/{$this->module}WebTest.php", <<<PHP
            <?php

            namespace Modules\\{$this->module}\Tests\Web;

            use Illuminate\Foundation\Testing\RefreshDatabase;
            use Modules\User\Models\User;
            use Tests\TestCase;

            class {$this->module}WebTest extends TestCase
            {
                use RefreshDatabase;

                public function test_guest_is_redirected_from_index(): void
                {
                    \$this->get('/{$this->slug()}')->assertRedirect();
                }

                public function test_authenticated_user_can_access_index(): void
                {
                    \$user = User::factory()->create();

                    \$this->actingAs(\$user)->get('/{$this->slug()}')->assertOk();
                }
            }
            PHP);
        }
    }

    private function createViews(): void
    {
        $slug  = Str::kebab(Str::plural($this->module));
        $title = Str::plural($this->module);

        $views = [
            'index'  => $this->indexViewStub($slug, $title),
            'show'   => $this->showViewStub($slug, $title),
            'create' => $this->createViewStub($slug, $title),
            'edit'   => $this->editViewStub($slug, $title),
        ];

        foreach ($views as $name => $content) {
            $this->write("Resources/views/{$slug}/{$name}.blade.php", $content);
        }
    }

    private function permSnake(): string
    {
        return Str::snake($this->module);
    }

    private function slug(): string
    {
        return Str::kebab(Str::plural($this->module));
    }

    private function indexViewStub(string $slug, string $title): string
    {
        return <<<BLADE
        @extends('admin.layouts.app')

        @section('title', '{$title}')

        @section('content')
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{$title}</h1>
                <a href="{{ route('{$slug}.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Created At</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse(\$items as \$item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ \$item->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ \$item->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 text-right text-sm space-x-2">
                                    <a href="{{ route('{$slug}.show', \$item->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    <a href="{{ route('{$slug}.edit', \$item->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                    <form action="{{ route('{$slug}.destroy', \$item->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endsection
        BLADE;
    }

    private function showViewStub(string $slug, string $title): string
    {
        return <<<BLADE
        @extends('admin.layouts.app')

        @section('title', '{$title} Details')

        @section('content')
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{$title} Details</h1>
                <a href="{{ route('{$slug}.index') }}" class="text-sm text-indigo-600">&larr; Back</a>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ \$item->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ \$item->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                </dl>
                <div class="mt-6 flex space-x-3">
                    <a href="{{ route('{$slug}.edit', \$item->id) }}" class="rounded-md bg-yellow-500 px-4 py-2 text-sm font-semibold text-white">Edit</a>
                    <form action="{{ route('{$slug}.destroy', \$item->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @endsection
        BLADE;
    }

    private function createViewStub(string $slug, string $title): string
    {
        return <<<BLADE
        @extends('admin.layouts.app')

        @section('title', 'Create {$title}')

        @section('content')
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create {$title}</h1>
                <a href="{{ route('{$slug}.index') }}" class="text-sm text-indigo-600">&larr; Back</a>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('{$slug}.store') }}" method="POST" class="space-y-4">
                    @csrf
                    {{-- Add your form fields here --}}
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Create</button>
                    </div>
                </form>
            </div>
        </div>
        @endsection
        BLADE;
    }

    private function editViewStub(string $slug, string $title): string
    {
        return <<<BLADE
        @extends('admin.layouts.app')

        @section('title', 'Edit {$title}')

        @section('content')
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit {$title}</h1>
                <a href="{{ route('{$slug}.show', \$item->id) }}" class="text-sm text-indigo-600">&larr; Back</a>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('{$slug}.update', \$item->id) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    {{-- Add your form fields here --}}
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
        @endsection
        BLADE;
    }

    private function registerTestSuite(): void
    {
        $phpunitPath = base_path('phpunit.xml');
        $content     = file_get_contents($phpunitPath);

        $apiSuite = "{$this->module}-Api";
        if (str_contains($content, "name=\"{$apiSuite}\"")) {
            return;
        }

        $webSuite = $this->isDomain()
            ? "\n        <testsuite name=\"{$this->module}-Web\">\n            <directory>modules/{$this->module}/Tests/Web</directory>\n        </testsuite>"
            : '';

        $suite = "        <testsuite name=\"{$this->module}-Api\">\n"
               . "            <directory>modules/{$this->module}/Tests/Api</directory>\n"
               . "        </testsuite>"
               . $webSuite;

        $content = preg_replace(
            '/(\s*<\/testsuites>)/',
            "\n{$suite}$1",
            $content,
        );

        file_put_contents($phpunitPath, $content);
    }

    private function registerProvider(): void
    {
        $providerClass = "Modules\\{$this->module}\\Providers\\{$this->module}ServiceProvider::class";
        $providersPath = base_path('bootstrap/providers.php');
        $content       = file_get_contents($providersPath);

        if (str_contains($content, $providerClass)) {
            return;
        }

        $content = preg_replace(
            '/(\n];)\s*$/',
            "\n    {$providerClass},$1\n",
            $content,
        );

        file_put_contents($providersPath, $content);
    }

    private function write(string $relativePath, string $content): void
    {
        $path = "{$this->modulePath}/{$relativePath}";

        $lines     = explode("\n", $content);
        $minIndent = PHP_INT_MAX;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $minIndent = min($minIndent, strlen($line) - strlen(ltrim($line)));
        }

        if ($minIndent > 0 && $minIndent < PHP_INT_MAX) {
            $lines = array_map(
                fn ($line) => trim($line) === '' ? '' : substr($line, $minIndent),
                $lines,
            );
        }

        file_put_contents($path, implode("\n", $lines) . "\n");
    }
}
