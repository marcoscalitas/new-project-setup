<?php

namespace Tests\Feature;

use Tests\TestCase;

class MakeModuleCommandTest extends TestCase
{
    private string $modulePath;
    private string $providersBackup;
    private string $phpunitBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modulePath = base_path('modules/Dummy');
        $this->providersBackup = file_get_contents(base_path('bootstrap/providers.php'));
        $this->phpunitBackup = file_get_contents(base_path('phpunit.xml'));
    }

    protected function tearDown(): void
    {
        $this->cleanupModule('Dummy');
        $this->cleanupModule('DummyTwo');
        $this->cleanupModule('Category');
        file_put_contents(base_path('bootstrap/providers.php'), $this->providersBackup);
        file_put_contents(base_path('phpunit.xml'), $this->phpunitBackup);

        parent::tearDown();
    }

    // == CREATION ==

    public function test_creates_module_with_all_directories(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $expectedDirs = [
            'Database/Migrations',
            'Database/Seeders',
            'Events',
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',
            'Jobs',
            'Listeners',
            'Models',
            'Policies',
            'Providers',
            'Routes',
            'Services',
            'Tests/Api',
            'Tests/Web',
        ];

        foreach ($expectedDirs as $dir) {
            $this->assertDirectoryExists("{$this->modulePath}/{$dir}");
        }
    }

    public function test_creates_all_expected_files(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $expectedFiles = [
            'Providers/DummyServiceProvider.php',
            'Models/Dummy.php',
            'Http/Controllers/DummyController.php',
            'Http/Requests/StoreDummyRequest.php',
            'Http/Requests/UpdateDummyRequest.php',
            'Http/Resources/DummyResource.php',
            'Jobs/ProcessDummyJob.php',
            'Policies/DummyPolicy.php',
            'Services/DummyService.php',
            'Routes/api.php',
            'Routes/web.php',
            'Tests/Api/DummyTest.php',
            'Tests/Web/DummyWebTest.php',
            'Database/Factories/DummyFactory.php',
        ];

        $migrationFiles = glob("{$this->modulePath}/Database/Migrations/*_create_dummies_table.php");
        $this->assertNotEmpty($migrationFiles, 'Migration file was not created');

        foreach ($expectedFiles as $file) {
            $this->assertFileExists("{$this->modulePath}/{$file}");
        }
    }

    public function test_creates_gitkeep_only_in_empty_directories(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $shouldHaveGitkeep = [
            'Listeners',
        ];

        $shouldNotHaveGitkeep = [
            'Database/Migrations',
            'Database/Factories',
            'Database/Seeders',
            'Events',
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',
            'Jobs',
            'Models',
            'Policies',
            'Providers',
            'Routes',
            'Services',
        ];

        foreach ($shouldHaveGitkeep as $dir) {
            $this->assertFileExists("{$this->modulePath}/{$dir}/.gitkeep");
        }

        foreach ($shouldNotHaveGitkeep as $dir) {
            $this->assertFileDoesNotExist("{$this->modulePath}/{$dir}/.gitkeep");
        }
    }

    // == PROVIDER REGISTRATION ==

    public function test_registers_provider_in_bootstrap_providers(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $providers = file_get_contents(base_path('bootstrap/providers.php'));

        $this->assertStringContainsString(
            'Modules\Dummy\Providers\DummyServiceProvider::class',
            $providers,
        );
    }

    public function test_does_not_duplicate_provider_on_second_run(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);
        $this->cleanupModule('Dummy');

        $this->artisan('make:module', ['name' => 'Dummy']);

        $providers = file_get_contents(base_path('bootstrap/providers.php'));
        $count = substr_count($providers, 'Modules\Dummy\Providers\DummyServiceProvider::class');

        $this->assertSame(1, $count);
    }

    // == NAMESPACES ==

    public function test_generated_files_have_correct_namespaces(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $this->assertFileContains('namespace Modules\Dummy\Providers;', 'Providers/DummyServiceProvider.php');
        $this->assertFileContains('namespace Modules\Dummy\Models;', 'Models/Dummy.php');
        $this->assertFileContains('namespace Modules\Dummy\Http\Controllers;', 'Http/Controllers/DummyController.php');
        $this->assertFileContains('namespace Modules\Dummy\Services;', 'Services/DummyService.php');
        $this->assertFileContains('namespace Modules\Dummy\Policies;', 'Policies/DummyPolicy.php');
        $this->assertFileContains('namespace Modules\Dummy\Http\Requests;', 'Http/Requests/StoreDummyRequest.php');
        $this->assertFileContains('namespace Modules\Dummy\Http\Resources;', 'Http/Resources/DummyResource.php');
    }

    // == VALIDATION ==

    public function test_fails_if_module_already_exists(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertFailed();
    }

    // == NAME NORMALIZATION ==

    public function test_normalizes_module_name_to_studly_case(): void
    {
        $this->artisan('make:module', ['name' => 'dummy-two'])
            ->assertSuccessful();

        $this->assertDirectoryExists(base_path('modules/DummyTwo'));
        $this->assertFileExists(base_path('modules/DummyTwo/Models/DummyTwo.php'));
    }

    // == FILE CONTENT ==

    public function test_generated_files_are_valid_php(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $phpFiles = [
            'Providers/DummyServiceProvider.php',
            'Models/Dummy.php',
            'Http/Controllers/DummyController.php',
            'Http/Requests/StoreDummyRequest.php',
            'Http/Requests/UpdateDummyRequest.php',
            'Http/Resources/DummyResource.php',
            'Policies/DummyPolicy.php',
            'Services/DummyService.php',
            'Database/Factories/DummyFactory.php',
        ];

        foreach ($phpFiles as $file) {
            $content = file_get_contents("{$this->modulePath}/{$file}");
            $this->assertStringStartsWith('<?php', $content, "File {$file} does not start with <?php");
        }
    }

    public function test_controller_injects_service_and_uses_resource(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Http/Controllers/DummyController.php");

        $this->assertStringContainsString('use Modules\Dummy\Services\DummyService;', $content);
        $this->assertStringContainsString('use Modules\Dummy\Http\Resources\DummyResource;', $content);
        $this->assertStringContainsString('private DummyService $dummyService', $content);
        $this->assertStringContainsString('DummyResource::collection', $content);
        $this->assertStringContainsString('new DummyResource(', $content);
    }

    public function test_service_uses_correct_model(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Services/DummyService.php");

        $this->assertStringContainsString('use Modules\Dummy\Models\Dummy;', $content);
        $this->assertStringContainsString('Dummy::paginate(', $content);
        $this->assertStringContainsString('Dummy::findOrFail($id)', $content);
        $this->assertStringContainsString('Dummy::create($data)', $content);
    }

    public function test_policy_uses_snake_case_permissions(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Policies/DummyPolicy.php");

        // Policy extends BasePolicy; only the prefix is declared here
        $this->assertStringContainsString('extends BasePolicy', $content);
        $this->assertStringContainsString("return 'dummy'", $content);
    }

    public function test_routes_use_plural_kebab_slug(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $apiContent = file_get_contents("{$this->modulePath}/Routes/api.php");
        $webContent = file_get_contents("{$this->modulePath}/Routes/web.php");

        $this->assertStringContainsString("apiResource('dummies'", $apiContent);
        $this->assertStringContainsString("prefix('dummies')", $webContent);
        $this->assertStringContainsString("name('dummies.", $webContent);
    }

    public function test_provider_loads_routes_and_migrations(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Providers/DummyServiceProvider.php");

        $this->assertStringContainsString("Routes/web.php", $content);
        $this->assertStringContainsString("Routes/api.php", $content);
        $this->assertStringContainsString('loadMigrationsFrom', $content);
    }

    public function test_multi_word_module_has_correct_snake_case_permissions(): void
    {
        $this->artisan('make:module', ['name' => 'dummy-two']);

        $content = file_get_contents(base_path('modules/DummyTwo/Policies/DummyTwoPolicy.php'));

        // Policy extends BasePolicy; prefix uses snake_case of module name
        $this->assertStringContainsString('extends BasePolicy', $content);
        $this->assertStringContainsString("return 'dummy_two'", $content);
    }

    public function test_irregular_plural_in_routes(): void
    {
        $this->cleanupModule('Category');

        $this->artisan('make:module', ['name' => 'Category']);

        $apiContent = file_get_contents(base_path('modules/Category/Routes/api.php'));
        $webContent = file_get_contents(base_path('modules/Category/Routes/web.php'));

        $this->assertStringContainsString("apiResource('categories'", $apiContent);
        $this->assertStringContainsString("prefix('categories')", $webContent);

        $this->cleanupModule('Category');
    }

    public function test_generated_test_files_have_correct_structure(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $apiTest = file_get_contents("{$this->modulePath}/Tests/Api/DummyTest.php");
        $webTest = file_get_contents("{$this->modulePath}/Tests/Web/DummyWebTest.php");

        $this->assertStringContainsString('namespace Modules\Dummy\Tests\Api;', $apiTest);
        $this->assertStringContainsString('class DummyTest extends TestCase', $apiTest);
        $this->assertStringContainsString('use RefreshDatabase;', $apiTest);

        $this->assertStringContainsString('namespace Modules\Dummy\Tests\Web;', $webTest);
        $this->assertStringContainsString('class DummyWebTest extends TestCase', $webTest);
        $this->assertStringContainsString('use RefreshDatabase;', $webTest);
    }

    public function test_generated_tests_are_decoupled_from_auth(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $apiTest = file_get_contents("{$this->modulePath}/Tests/Api/DummyTest.php");
        $webTest = file_get_contents("{$this->modulePath}/Tests/Web/DummyWebTest.php");

        // API test uses Passport for bearer token authentication
        $this->assertStringContainsString('Laravel\Passport\Client', $apiTest);
        $this->assertStringContainsString('createToken', $apiTest);
        // Web test uses actingAs (session-based), no Passport
        $this->assertStringNotContainsString('Passport', $webTest);
        $this->assertStringNotContainsString('createToken', $webTest);
        $this->assertStringContainsString('actingAs', $webTest);
    }

    // == NAME VALIDATION ==

    public function test_fails_with_empty_name(): void
    {
        $this->artisan('make:module', ['name' => ''])
            ->assertFailed();
    }

    public function test_fails_with_invalid_characters_in_name(): void
    {
        $this->artisan('make:module', ['name' => 'my/module'])
            ->assertFailed();

        $this->assertDirectoryDoesNotExist(base_path('modules/My'));
    }

    // == FILE STRUCTURE DETAILS ==

    public function test_controller_has_all_crud_methods(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Http/Controllers/DummyController.php");

        $this->assertStringContainsString('public function index(Request $request)', $content);
        $this->assertStringContainsString('public function store(StoreDummyRequest $request)', $content);
        $this->assertStringContainsString('public function show(int $id)', $content);
        $this->assertStringContainsString('public function update(UpdateDummyRequest $request, int $id)', $content);
        $this->assertStringContainsString('public function destroy(int $id)', $content);
    }

    public function test_api_routes_use_resource_without_prefix_duplication(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Routes/api.php");

        $this->assertStringContainsString("apiResource('dummies'", $content);
        $this->assertStringNotContainsString("->prefix(", $content);
    }

    public function test_web_routes_define_all_named_crud_routes(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Routes/web.php");

        $this->assertStringContainsString("name('dummies.index')", $content);
        $this->assertStringContainsString("name('dummies.store')", $content);
        $this->assertStringContainsString("name('dummies.show')", $content);
        $this->assertStringContainsString("name('dummies.update')", $content);
        $this->assertStringContainsString("name('dummies.destroy')", $content);
    }

    public function test_routes_have_correct_middleware(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $api = file_get_contents("{$this->modulePath}/Routes/api.php");
        $web = file_get_contents("{$this->modulePath}/Routes/web.php");

        $this->assertStringContainsString("'auth:api'", $api);
        $this->assertStringContainsString("'throttle:60,1'", $api);
        $this->assertStringContainsString("'auth'", $web);
        $this->assertStringContainsString("'throttle:60,1'", $web);
    }

    public function test_model_has_correct_base_class_and_traits(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Models/Dummy.php");

        $this->assertStringContainsString('extends BaseModel', $content);
        $this->assertStringContainsString('use Modules\\Core\\Models\\BaseModel;', $content);
        $this->assertStringContainsString('protected $fillable', $content);
    }

    public function test_model_uses_module_factory(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Models/Dummy.php");

        $this->assertStringContainsString('use Modules\\Dummy\\Database\\Factories\\DummyFactory;', $content);
        $this->assertStringContainsString('protected static function newFactory(): Factory', $content);
        $this->assertStringContainsString('DummyFactory::new()', $content);
    }

    public function test_generates_migration_with_correct_table_name(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $files = glob("{$this->modulePath}/Database/Migrations/*_create_dummies_table.php");
        $this->assertCount(1, $files, 'Expected exactly one migration file');

        $content = file_get_contents($files[0]);
        $this->assertStringContainsString("Schema::create('dummies'", $content);
        $this->assertStringContainsString('$table->id()', $content);
        $this->assertStringContainsString('$table->timestamps()', $content);
        $this->assertStringContainsString('$table->softDeletes()', $content);
        $this->assertStringContainsString("Schema::dropIfExists('dummies')", $content);
    }

    public function test_generates_factory_with_correct_model(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Database/Factories/DummyFactory.php");

        $this->assertStringContainsString('namespace Modules\\Dummy\\Database\\Factories;', $content);
        $this->assertStringContainsString('use Modules\\Dummy\\Models\\Dummy;', $content);
        $this->assertStringContainsString('class DummyFactory extends Factory', $content);
        $this->assertStringContainsString('protected $model = Dummy::class;', $content);
        $this->assertStringContainsString('public function definition(): array', $content);
    }

    public function test_provider_has_register_and_boot_methods(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Providers/DummyServiceProvider.php");

        $this->assertStringContainsString('extends ServiceProvider', $content);
        $this->assertStringContainsString('public function register(): void', $content);
        $this->assertStringContainsString('public function boot(): void', $content);
    }

    public function test_form_requests_extend_form_request_with_required_methods(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        foreach (['StoreDummyRequest.php', 'UpdateDummyRequest.php'] as $file) {
            $content = file_get_contents("{$this->modulePath}/Http/Requests/{$file}");

            $this->assertStringContainsString('extends FormRequest', $content, "{$file} must extend FormRequest");
            $this->assertStringContainsString('public function authorize(): bool', $content, "{$file} must have authorize");
            $this->assertStringContainsString('public function rules(): array', $content, "{$file} must have rules");
        }
    }

    public function test_resource_extends_json_resource_with_to_array(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Http/Resources/DummyResource.php");

        // Resource extends BaseResource (which itself extends JsonResource)
        $this->assertStringContainsString('extends BaseResource', $content);
        $this->assertStringContainsString('public function toArray(Request $request): array', $content);
        // BaseResource::base() provides id + timestamps; toArray calls $this->base()
        $this->assertStringContainsString('$this->base()', $content);
    }

    // == JOB ==

    public function test_generates_job_with_correct_structure(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Jobs/ProcessDummyJob.php");

        $this->assertStringContainsString('namespace Modules\\Dummy\\Jobs;', $content);
        $this->assertStringContainsString('class ProcessDummyJob implements ShouldQueue', $content);
        $this->assertStringContainsString('use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;', $content);
        $this->assertStringContainsString('public int $tries = 3;', $content);
        $this->assertStringContainsString('public int $timeout = 60;', $content);
        $this->assertStringContainsString('public function handle(): void', $content);
        $this->assertStringContainsString('public function failed(Throwable $e): void', $content);
    }

    public function test_generated_job_is_valid_php(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);

        $content = file_get_contents("{$this->modulePath}/Jobs/ProcessDummyJob.php");

        $this->assertStringStartsWith('<?php', $content);
    }

    // == PHPUNIT.XML REGISTRATION ==

    public function test_registers_test_suite_in_phpunit_xml(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $phpunit = file_get_contents(base_path('phpunit.xml'));

        $this->assertStringContainsString('name="Dummy-Api"', $phpunit);
        $this->assertStringContainsString('modules/Dummy/Tests/Api', $phpunit);
        $this->assertStringContainsString('name="Dummy-Web"', $phpunit);
        $this->assertStringContainsString('modules/Dummy/Tests/Web', $phpunit);
    }

    public function test_does_not_duplicate_test_suite_on_second_run(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);
        $this->cleanupModule('Dummy');

        $this->artisan('make:module', ['name' => 'Dummy']);

        $phpunit = file_get_contents(base_path('phpunit.xml'));
        $count = substr_count($phpunit, 'name="Dummy-Api"');

        $this->assertSame(1, $count);
    }

    // == MULTI-MODULE ==

    public function test_multiple_modules_registered_correctly(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy']);
        $this->artisan('make:module', ['name' => 'DummyTwo']);

        $providers = file_get_contents(base_path('bootstrap/providers.php'));
        $phpunit = file_get_contents(base_path('phpunit.xml'));

        $this->assertStringContainsString('DummyServiceProvider::class', $providers);
        $this->assertStringContainsString('DummyTwoServiceProvider::class', $providers);
        $this->assertStringContainsString('name="Dummy-Api"', $phpunit);
        $this->assertStringContainsString('name="DummyTwo-Api"', $phpunit);
    }

    // == --WITH-VIEWS FLAG ==

    public function test_with_views_creates_views_directory_and_files(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy', '--with-views' => true])
            ->assertSuccessful();

        $this->assertDirectoryExists("{$this->modulePath}/Resources/views/dummies");
        $this->assertFileExists("{$this->modulePath}/Resources/views/dummies/index.blade.php");
        $this->assertFileExists("{$this->modulePath}/Resources/views/dummies/show.blade.php");
        $this->assertFileExists("{$this->modulePath}/Resources/views/dummies/create.blade.php");
        $this->assertFileExists("{$this->modulePath}/Resources/views/dummies/edit.blade.php");
    }

    public function test_with_views_provider_loads_views(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy', '--with-views' => true])
            ->assertSuccessful();

        $content = file_get_contents("{$this->modulePath}/Providers/DummyServiceProvider.php");

        $this->assertStringContainsString('loadViewsFrom', $content);
        $this->assertStringContainsString("'dummy'", $content);
    }

    public function test_with_views_controller_has_dual_response(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy', '--with-views' => true])
            ->assertSuccessful();

        $content = file_get_contents("{$this->modulePath}/Http/Controllers/DummyController.php");

        $this->assertStringContainsString('request()->expectsJson()', $content);
        $this->assertStringContainsString('public function create()', $content);
        $this->assertStringContainsString('public function edit(int $id)', $content);
        $this->assertStringContainsString("view('dummy::dummies.", $content);
        $this->assertStringContainsString('RedirectResponse', $content);
    }

    public function test_with_views_web_routes_have_create_and_edit(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy', '--with-views' => true])
            ->assertSuccessful();

        $content = file_get_contents("{$this->modulePath}/Routes/web.php");

        $this->assertStringContainsString("name('dummies.create')", $content);
        $this->assertStringContainsString("name('dummies.edit')", $content);
    }

    public function test_with_views_views_contain_correct_routes(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy', '--with-views' => true])
            ->assertSuccessful();

        $index = file_get_contents("{$this->modulePath}/Resources/views/dummies/index.blade.php");

        $this->assertStringContainsString("route('dummies.create')", $index);
        $this->assertStringContainsString("route('dummies.show'", $index);
        $this->assertStringContainsString("route('dummies.edit'", $index);
        $this->assertStringContainsString("route('dummies.destroy'", $index);
        $this->assertStringContainsString('Dummies', $index);
    }

    public function test_without_views_does_not_create_views_directory(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $this->assertDirectoryDoesNotExist("{$this->modulePath}/Resources");
    }

    public function test_without_views_controller_is_api_only(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $content = file_get_contents("{$this->modulePath}/Http/Controllers/DummyController.php");

        $this->assertStringNotContainsString('expectsJson', $content);
        $this->assertStringNotContainsString('public function create()', $content);
        $this->assertStringNotContainsString('public function edit(', $content);
    }

    public function test_without_views_provider_does_not_load_views(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $content = file_get_contents("{$this->modulePath}/Providers/DummyServiceProvider.php");

        $this->assertStringNotContainsString('loadViewsFrom', $content);
    }

    public function test_without_views_web_routes_have_no_create_edit(): void
    {
        $this->artisan('make:module', ['name' => 'Dummy'])
            ->assertSuccessful();

        $content = file_get_contents("{$this->modulePath}/Routes/web.php");

        $this->assertStringNotContainsString("name('dummies.create')", $content);
        $this->assertStringNotContainsString("name('dummies.edit')", $content);
    }

    public function test_with_views_multi_word_module(): void
    {
        $this->artisan('make:module', ['name' => 'dummy-two', '--with-views' => true])
            ->assertSuccessful();

        $modulePath = base_path('modules/DummyTwo');

        $this->assertDirectoryExists("{$modulePath}/Resources/views/dummy-twos");
        $this->assertFileExists("{$modulePath}/Resources/views/dummy-twos/index.blade.php");

        $content = file_get_contents("{$modulePath}/Http/Controllers/DummyTwoController.php");
        $this->assertStringContainsString('request()->expectsJson()', $content);
        $this->assertStringContainsString("view('dummytwo::dummy-twos.", $content);
    }

    // == HELPERS ==

    private function assertFileContains(string $expected, string $relativePath): void
    {
        $content = file_get_contents("{$this->modulePath}/{$relativePath}");
        $this->assertStringContainsString($expected, $content);
    }

    private function cleanupModule(string $name): void
    {
        $path = base_path("modules/{$name}");

        if (!is_dir($path)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($path);
    }
}
