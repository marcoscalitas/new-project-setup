<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The module name (e.g. Product)}';

    protected $description = 'Generate a new module following the project pattern';

    private string $module;
    private string $modulePath;

    public function handle(): int
    {
        $this->module = Str::studly($this->argument('name'));
        $this->modulePath = base_path("modules/{$this->module}");

        if (empty($this->module) || !preg_match('/^[A-Z][a-zA-Z0-9]*$/', $this->module)) {
            $this->error('Please provide a valid module name (letters and numbers only).');
            return self::FAILURE;
        }

        if (is_dir($this->modulePath)) {
            $this->error("Module [{$this->module}] already exists!");
            return self::FAILURE;
        }

        $this->createDirectories();
        $this->createProvider();
        $this->createModel();
        $this->createController();
        $this->createService();
        $this->createRequests();
        $this->createResource();
        $this->createPolicy();
        $this->createRoutes();
        $this->createTests();
        $this->registerProvider();
        $this->registerTestSuite();

        $this->components->info("Module [{$this->module}] created successfully.");

        $this->components->bulletList([
            "Provider registered in <comment>bootstrap/providers.php</comment>",
            "Test suite registered in <comment>phpunit.xml</comment>",
            "Add event bindings to <comment>app/Providers/EventServiceProvider.php</comment>",
            "Add seeder call to <comment>database/seeders/DatabaseSeeder.php</comment> if needed",
            "Run <comment>php artisan migrate</comment> after creating migrations",
        ]);

        return self::SUCCESS;
    }

    private function createDirectories(): void
    {
        $dirs = [
            'Actions',
            'Database/Migrations',
            'Database/Seeders',
            'Events',
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',
            'Listeners',
            'Models',
            'Policies',
            'Providers',
            'Routes',
            'Services',
            'Tests/Web',
        ];

        $fileWillExist = [
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',
            'Models',
            'Policies',
            'Providers',
            'Routes',
            'Services',
            'Tests',
            'Tests/Web',
        ];

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
        $this->write('Providers/' . $this->module . 'ServiceProvider.php', <<<PHP
        <?php

        namespace Modules\\{$this->module}\Providers;

        use Illuminate\Support\Facades\Route;
        use Illuminate\Support\ServiceProvider;

        class {$this->module}ServiceProvider extends ServiceProvider
        {
            public function register(): void
            {
                //
            }

            public function boot(): void
            {
                Route::middleware('web')
                    ->group(__DIR__ . '/../Routes/web.php');

                Route::prefix('api')
                    ->middleware('api')
                    ->group(__DIR__ . '/../Routes/api.php');

                \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
            }
        }
        PHP);
    }

    private function createModel(): void
    {
        $this->write("Models/{$this->module}.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Models;

        use Illuminate\Database\Eloquent\Factories\HasFactory;
        use Illuminate\Database\Eloquent\Model;

        class {$this->module} extends Model
        {
            use HasFactory;

            protected \$fillable = [
                //
            ];
        }
        PHP);
    }

    private function createController(): void
    {
        $lower = Str::camel($this->module);

        $this->write("Http/Controllers/{$this->module}Controller.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Controllers;

        use Illuminate\Http\JsonResponse;
        use Modules\\{$this->module}\Http\Requests\Store{$this->module}Request;
        use Modules\\{$this->module}\Http\Requests\Update{$this->module}Request;
        use Modules\\{$this->module}\Http\Resources\\{$this->module}Resource;
        use Modules\\{$this->module}\Services\\{$this->module}Service;

        class {$this->module}Controller
        {
            public function __construct(private {$this->module}Service \${$lower}Service)
            {
            }

            public function index(): JsonResponse
            {
                \$items = \$this->{$lower}Service->getAll();

                return response()->json({$this->module}Resource::collection(\$items));
            }

            public function store(Store{$this->module}Request \$request): JsonResponse
            {
                \$item = \$this->{$lower}Service->create(\$request->validated());

                return response()->json(new {$this->module}Resource(\$item), 201);
            }

            public function show(int \$id): JsonResponse
            {
                \$item = \$this->{$lower}Service->findById(\$id);

                return response()->json(new {$this->module}Resource(\$item));
            }

            public function update(Update{$this->module}Request \$request, int \$id): JsonResponse
            {
                \$item = \$this->{$lower}Service->update(\$id, \$request->validated());

                return response()->json(new {$this->module}Resource(\$item));
            }

            public function destroy(int \$id): JsonResponse
            {
                \$this->{$lower}Service->delete(\$id);

                return response()->json(null, 204);
            }
        }
        PHP);
    }

    private function createService(): void
    {
        $this->write("Services/{$this->module}Service.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Services;

        use Modules\\{$this->module}\Models\\{$this->module};

        class {$this->module}Service
        {
            public function getAll()
            {
                return {$this->module}::all();
            }

            public function findById(int \$id): {$this->module}
            {
                return {$this->module}::findOrFail(\$id);
            }

            public function create(array \$data): {$this->module}
            {
                return {$this->module}::create(\$data);
            }

            public function update(int \$id, array \$data): {$this->module}
            {
                \$item = {$this->module}::findOrFail(\$id);
                \$item->update(\$data);

                return \$item;
            }

            public function delete(int \$id): void
            {
                \$item = {$this->module}::findOrFail(\$id);
                \$item->delete();
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
                    'id' => \$this->id,
                    'created_at' => \$this->created_at,
                    'updated_at' => \$this->updated_at,
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
        use Modules\\{$this->module}\Models\\{$this->module};

        class {$this->module}Policy
        {
            public function viewAny(User \$user): bool
            {
                return \$user->hasPermissionTo('{$lower}.list');
            }

            public function view(User \$user, {$this->module} \$item): bool
            {
                return \$user->hasPermissionTo('{$lower}.view');
            }

            public function create(User \$user): bool
            {
                return \$user->hasPermissionTo('{$lower}.create');
            }

            public function update(User \$user, {$this->module} \$item): bool
            {
                return \$user->hasPermissionTo('{$lower}.update');
            }

            public function delete(User \$user, {$this->module} \$item): bool
            {
                return \$user->hasPermissionTo('{$lower}.delete');
            }
        }
        PHP);
    }

    private function createRoutes(): void
    {
        $lower = Str::camel($this->module);
        $slug = Str::kebab(Str::plural($this->module));

        $this->write('Routes/api.php', <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;
        use Modules\\{$this->module}\Http\Controllers\\{$this->module}Controller;

        Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
            Route::apiResource('{$slug}', {$this->module}Controller::class);
        });
        PHP);

        $this->write('Routes/web.php', <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;
        use Modules\\{$this->module}\Http\Controllers\\{$this->module}Controller;

        Route::prefix('{$slug}')
            ->middleware(['auth', 'throttle:60,1'])
            ->group(function () {
                Route::get('/',         [{$this->module}Controller::class, 'index'])->name('{$slug}.index');
                Route::post('/',        [{$this->module}Controller::class, 'store'])->name('{$slug}.store');
                Route::get('/{id}',     [{$this->module}Controller::class, 'show'])->name('{$slug}.show');
                Route::put('/{id}',     [{$this->module}Controller::class, 'update'])->name('{$slug}.update');
                Route::delete('/{id}',  [{$this->module}Controller::class, 'destroy'])->name('{$slug}.destroy');
            });
        PHP);
    }

    private function createTests(): void
    {
        $slug = Str::kebab(Str::plural($this->module));

        $this->write("Tests/{$this->module}Test.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Tests;

        use Illuminate\Foundation\Testing\RefreshDatabase;
        use Tests\TestCase;

        class {$this->module}Test extends TestCase
        {
            use RefreshDatabase;

            public function test_example(): void
            {
                \$this->assertTrue(true);
            }
        }
        PHP);

        $this->write("Tests/Web/{$this->module}WebTest.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Tests\Web;

        use Illuminate\Foundation\Testing\RefreshDatabase;
        use Tests\TestCase;

        class {$this->module}WebTest extends TestCase
        {
            use RefreshDatabase;

            public function test_example(): void
            {
                \$this->assertTrue(true);
            }
        }
        PHP);
    }

    private function registerTestSuite(): void
    {
        $phpunitPath = base_path('phpunit.xml');
        $content = file_get_contents($phpunitPath);

        $suiteName = $this->module;
        if (str_contains($content, "name=\"{$suiteName}\"")) {
            return;
        }

        $suite = "        <testsuite name=\"{$suiteName}\">\n"
               . "            <directory>modules/{$this->module}/Tests</directory>\n"
               . "        </testsuite>";

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
        $content = file_get_contents($providersPath);

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

        // Remove leading indentation from heredoc
        $lines = explode("\n", $content);
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
