<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The module name (e.g. Product)} {--with-views : Generate Blade views and dual response controller}';

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

        if ($this->option('with-views')) {
            $this->createViews();
        }

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
        $slug = Str::kebab(Str::plural($this->module));

        $dirs = [
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
            'Tests/Api',
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
            'Tests/Api',
            'Tests/Web',
        ];

        if ($this->option('with-views')) {
            $dirs[] = "Resources/views/{$slug}";
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
        $namespace = Str::lower($this->module);
        $viewsLine = $this->option('with-views')
            ? "\n        \$this->loadViewsFrom(__DIR__ . '/../Resources/views', '{$namespace}');"
            : '';

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

                \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');{$viewsLine}
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
        use Illuminate\Database\Eloquent\SoftDeletes;

        class {$this->module} extends Model
        {
            use HasFactory, SoftDeletes;

            protected \$fillable = [
                //
            ];
        }
        PHP);
    }

    private function createController(): void
    {
        $lower = Str::camel($this->module);

        if ($this->option('with-views')) {
            $this->createDualController();
            return;
        }

        $this->write("Http/Controllers/{$this->module}Controller.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Controllers;

        use Illuminate\Http\JsonResponse;
        use Illuminate\Http\Request;
        use Modules\\{$this->module}\Http\Requests\Store{$this->module}Request;
        use Modules\\{$this->module}\Http\Requests\Update{$this->module}Request;
        use Modules\\{$this->module}\Http\Resources\\{$this->module}Resource;
        use Modules\\{$this->module}\Services\\{$this->module}Service;

        class {$this->module}Controller
        {
            public function __construct(private {$this->module}Service \${$lower}Service)
            {
            }

            public function index(Request \$request): JsonResponse
            {
                \$perPage = min((int) \$request->query('per_page', 15), 100);
                \$items = \$this->{$lower}Service->getAll(\$perPage);

                return {$this->module}Resource::collection(\$items)->response();
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

    private function createDualController(): void
    {
        $lower = Str::camel($this->module);
        $slug = Str::kebab(Str::plural($this->module));
        $namespace = Str::lower($this->module);

        $this->write("Http/Controllers/{$this->module}Controller.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Http\Controllers;

        use Illuminate\Http\JsonResponse;
        use Illuminate\Http\RedirectResponse;
        use Illuminate\Http\Request;
        use Illuminate\View\View;
        use Modules\\{$this->module}\Http\Requests\Store{$this->module}Request;
        use Modules\\{$this->module}\Http\Requests\Update{$this->module}Request;
        use Modules\\{$this->module}\Http\Resources\\{$this->module}Resource;
        use Modules\\{$this->module}\Services\\{$this->module}Service;

        class {$this->module}Controller
        {
            public function __construct(private {$this->module}Service \${$lower}Service)
            {
            }

            public function index(Request \$request): JsonResponse|View
            {
                \$perPage = min((int) \$request->query('per_page', 15), 100);
                \$items = \$this->{$lower}Service->getAll(\$perPage);

                if (\$request->expectsJson()) {
                    return {$this->module}Resource::collection(\$items)->response();
                }

                return view('{$namespace}::{$slug}.index', compact('items'));
            }

            public function create(): View
            {
                return view('{$namespace}::{$slug}.create');
            }

            public function store(Store{$this->module}Request \$request): JsonResponse|RedirectResponse
            {
                \$item = \$this->{$lower}Service->create(\$request->validated());

                if (request()->expectsJson()) {
                    return response()->json(new {$this->module}Resource(\$item), 201);
                }

                return redirect()->route('{$slug}.index')->with('success', '{$this->module} created successfully.');
            }

            public function show(int \$id): JsonResponse|View
            {
                \$item = \$this->{$lower}Service->findById(\$id);

                if (request()->expectsJson()) {
                    return response()->json(new {$this->module}Resource(\$item));
                }

                return view('{$namespace}::{$slug}.show', compact('item'));
            }

            public function edit(int \$id): View
            {
                \$item = \$this->{$lower}Service->findById(\$id);

                return view('{$namespace}::{$slug}.edit', compact('item'));
            }

            public function update(Update{$this->module}Request \$request, int \$id): JsonResponse|RedirectResponse
            {
                \$item = \$this->{$lower}Service->update(\$id, \$request->validated());

                if (request()->expectsJson()) {
                    return response()->json(new {$this->module}Resource(\$item));
                }

                return redirect()->route('{$slug}.show', \$item->id)->with('success', '{$this->module} updated successfully.');
            }

            public function destroy(int \$id): JsonResponse|RedirectResponse
            {
                \$this->{$lower}Service->delete(\$id);

                if (request()->expectsJson()) {
                    return response()->json(null, 204);
                }

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

        use Modules\\{$this->module}\Models\\{$this->module};

        class {$this->module}Service
        {
            public function getAll(int \$perPage = 15): \\Illuminate\\Contracts\\Pagination\\LengthAwarePaginator
            {
                return {$this->module}::paginate(\$perPage);
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

        if ($this->option('with-views')) {
            $this->createWebRoutesWithViews($slug);
        } else {
            $this->createWebRoutes($slug);
        }
    }

    private function createWebRoutes(string $slug): void
    {
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

    private function createWebRoutesWithViews(string $slug): void
    {
        $this->write('Routes/web.php', <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;
        use Modules\\{$this->module}\Http\Controllers\\{$this->module}Controller;

        Route::prefix('{$slug}')
            ->middleware(['auth', 'throttle:60,1'])
            ->group(function () {
                Route::get('/',         [{$this->module}Controller::class, 'index'])->name('{$slug}.index');
                Route::get('/create',   [{$this->module}Controller::class, 'create'])->name('{$slug}.create');
                Route::post('/',        [{$this->module}Controller::class, 'store'])->name('{$slug}.store');
                Route::get('/{id}',     [{$this->module}Controller::class, 'show'])->name('{$slug}.show');
                Route::get('/{id}/edit', [{$this->module}Controller::class, 'edit'])->name('{$slug}.edit');
                Route::put('/{id}',     [{$this->module}Controller::class, 'update'])->name('{$slug}.update');
                Route::delete('/{id}',  [{$this->module}Controller::class, 'destroy'])->name('{$slug}.destroy');
            });
        PHP);
    }

    private function createTests(): void
    {
        $slug = Str::kebab(Str::plural($this->module));

        $this->write("Tests/Api/{$this->module}Test.php", <<<PHP
        <?php

        namespace Modules\\{$this->module}\Tests\Api;

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

    private function createViews(): void
    {
        $slug = Str::kebab(Str::plural($this->module));

        $this->write("Resources/views/{$slug}/index.blade.php", <<<'BLADE'
        @extends('layouts.app')

        @section('title', '{{ title }}')

        @section('content')
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ title }}</h1>
                <a href="{{ route('{{ slug }}.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Create
                </a>
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
                        @forelse($items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $item->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 text-right text-sm space-x-2">
                                    <a href="{{ route('{{ slug }}.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">View</a>
                                    <a href="{{ route('{{ slug }}.edit', $item->id) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400">Edit</a>
                                    <form action="{{ route('{{ slug }}.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endsection
        BLADE);

        $this->write("Resources/views/{$slug}/show.blade.php", <<<'BLADE'
        @extends('layouts.app')

        @section('title', '{{ title }} Details')

        @section('content')
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ title }} Details</h1>
                <a href="{{ route('{{ slug }}.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">&larr; Back to list</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $item->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $item->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex space-x-3">
                    <a href="{{ route('{{ slug }}.edit', $item->id) }}" class="rounded-md bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-400">Edit</a>
                    <form action="{{ route('{{ slug }}.destroy', $item->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @endsection
        BLADE);

        $this->write("Resources/views/{$slug}/create.blade.php", <<<'BLADE'
        @extends('layouts.app')

        @section('title', 'Create {{ title }}')

        @section('content')
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create {{ title }}</h1>
                <a href="{{ route('{{ slug }}.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">&larr; Back to list</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('{{ slug }}.store') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Add your form fields here --}}

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create</button>
                    </div>
                </form>
            </div>
        </div>
        @endsection
        BLADE);

        $this->write("Resources/views/{$slug}/edit.blade.php", <<<'BLADE'
        @extends('layouts.app')

        @section('title', 'Edit {{ title }}')

        @section('content')
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit {{ title }}</h1>
                <a href="{{ route('{{ slug }}.show', $item->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">&larr; Back to details</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('{{ slug }}.update', $item->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Add your form fields here --}}

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
                    </div>
                </form>
            </div>
        </div>
        @endsection
        BLADE);

        // Replace placeholders with actual values
        $viewsPath = "{$this->modulePath}/Resources/views/{$slug}";
        $title = Str::plural($this->module);

        foreach (glob("{$viewsPath}/*.blade.php") as $file) {
            $content = file_get_contents($file);
            $content = str_replace('{{ title }}', $title, $content);
            $content = str_replace('{{ slug }}', $slug, $content);
            file_put_contents($file, $content);
        }
    }

    private function registerTestSuite(): void
    {
        $phpunitPath = base_path('phpunit.xml');
        $content = file_get_contents($phpunitPath);

        $apiSuite = "{$this->module}-Api";
        if (str_contains($content, "name=\"{$apiSuite}\"")) {
            return;
        }

        $suite = "        <testsuite name=\"{$this->module}-Api\">\n"
               . "            <directory>modules/{$this->module}/Tests/Api</directory>\n"
               . "        </testsuite>\n"
               . "        <testsuite name=\"{$this->module}-Web\">\n"
               . "            <directory>modules/{$this->module}/Tests/Web</directory>\n"
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
