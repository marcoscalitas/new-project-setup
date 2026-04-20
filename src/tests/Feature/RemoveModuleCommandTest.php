<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemoveModuleCommandTest extends TestCase
{
    private string $providersBackup;
    private string $phpunitBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providersBackup = file_get_contents(base_path('bootstrap/providers.php'));
        $this->phpunitBackup = file_get_contents(base_path('phpunit.xml'));
        $this->artisan('make:module', ['name' => 'Dummy']);
    }

    protected function tearDown(): void
    {
        $this->cleanupModule('Dummy');
        $this->cleanupModule('DummyTwo');
        file_put_contents(base_path('bootstrap/providers.php'), $this->providersBackup);
        file_put_contents(base_path('phpunit.xml'), $this->phpunitBackup);

        parent::tearDown();
    }

    // == REMOVAL ==

    public function test_removes_module_directory_with_force(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $this->assertDirectoryDoesNotExist(base_path('modules/Dummy'));
    }

    public function test_unregisters_provider_from_bootstrap(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $providers = file_get_contents(base_path('bootstrap/providers.php'));

        $this->assertStringNotContainsString(
            'Modules\Dummy\Providers\DummyServiceProvider::class',
            $providers,
        );
    }

    // == VALIDATION ==

    public function test_fails_if_module_does_not_exist(): void
    {
        $this->artisan('remove:module', ['name' => 'NonExistent', '--force' => true])
            ->assertFailed();
    }

    public function test_fails_for_protected_core_modules(): void
    {
        $protected = ['Auth', 'User', 'Permission', 'Notification'];

        foreach ($protected as $module) {
            $this->artisan('remove:module', ['name' => $module, '--force' => true])
                ->assertFailed();
        }
    }

    // == CONFIRMATION ==

    public function test_cancels_without_force_when_user_declines(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy'])
            ->expectsConfirmation(
                'This will permanently delete modules/Dummy/ and all its files. Continue?',
                'no',
            )
            ->assertSuccessful();

        $this->assertDirectoryExists(base_path('modules/Dummy'));
    }

    public function test_removes_when_user_confirms(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy'])
            ->expectsConfirmation(
                'This will permanently delete modules/Dummy/ and all its files. Continue?',
                'yes',
            )
            ->assertSuccessful();

        $this->assertDirectoryDoesNotExist(base_path('modules/Dummy'));
    }

    // == FULL CYCLE ==

    public function test_create_then_remove_leaves_clean_state(): void
    {
        $providersBefore = file_get_contents(base_path('bootstrap/providers.php'));

        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $providersAfter = file_get_contents(base_path('bootstrap/providers.php'));

        $this->assertDirectoryDoesNotExist(base_path('modules/Dummy'));
        $this->assertStringNotContainsString('Dummy', $providersAfter);
    }

    public function test_removal_does_not_corrupt_other_providers(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $providers = file_get_contents(base_path('bootstrap/providers.php'));

        $this->assertStringContainsString('Modules\Auth\Providers\AuthServiceProvider::class', $providers);
        $this->assertStringContainsString('Modules\User\Providers\UserServiceProvider::class', $providers);
        $this->assertStringContainsString('Modules\Permission\Providers\PermissionServiceProvider::class', $providers);
        $this->assertStringContainsString('Modules\Notification\Providers\NotificationServiceProvider::class', $providers);
    }

    public function test_removes_module_with_nested_files(): void
    {
        // Add extra nested content to simulate a developed module
        $nested = base_path('modules/Dummy/Http/Controllers/Api');
        mkdir($nested, 0755, true);
        file_put_contents("{$nested}/DummyApiController.php", '<?php // stub');

        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $this->assertDirectoryDoesNotExist(base_path('modules/Dummy'));
    }

    public function test_normalizes_name_on_removal(): void
    {
        // Module was created as 'Dummy' (StudlyCase), try removing with lowercase
        $this->artisan('remove:module', ['name' => 'dummy', '--force' => true])
            ->assertSuccessful();

        $this->assertDirectoryDoesNotExist(base_path('modules/Dummy'));
    }

    // == ISOLATION ==

    public function test_protection_works_with_lowercase_input(): void
    {
        $this->artisan('remove:module', ['name' => 'auth', '--force' => true])
            ->assertFailed();

        $this->artisan('remove:module', ['name' => 'user', '--force' => true])
            ->assertFailed();
    }

    public function test_removing_one_module_does_not_affect_another(): void
    {
        $this->artisan('make:module', ['name' => 'DummyTwo']);

        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $this->assertDirectoryDoesNotExist(base_path('modules/Dummy'));
        $this->assertDirectoryExists(base_path('modules/DummyTwo'));

        $providers = file_get_contents(base_path('bootstrap/providers.php'));
        $this->assertStringNotContainsString('DummyServiceProvider', $providers);
        $this->assertStringContainsString('DummyTwoServiceProvider', $providers);
    }

    public function test_full_cycle_restores_exact_provider_file(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $providersAfter = file_get_contents(base_path('bootstrap/providers.php'));

        $this->assertSame($this->providersBackup, $providersAfter);
    }

    // == PHPUNIT.XML ==

    public function test_unregisters_test_suite_from_phpunit_xml(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $phpunit = file_get_contents(base_path('phpunit.xml'));

        $this->assertStringNotContainsString('name="Dummy-Api"', $phpunit);
        $this->assertStringNotContainsString('name="Dummy-Web"', $phpunit);
        $this->assertStringNotContainsString('modules/Dummy/Tests', $phpunit);
    }

    public function test_removal_does_not_corrupt_other_test_suites(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $phpunit = file_get_contents(base_path('phpunit.xml'));

        $this->assertStringContainsString('name="Feature"', $phpunit);
        $this->assertStringContainsString('name="Auth-Web"', $phpunit);
        $this->assertStringContainsString('name="User-Api"', $phpunit);
        $this->assertStringContainsString('name="User-Web"', $phpunit);
        $this->assertStringContainsString('name="Permission-Api"', $phpunit);
        $this->assertStringContainsString('name="Permission-Web"', $phpunit);
        $this->assertStringContainsString('name="Notification-Api"', $phpunit);
        $this->assertStringContainsString('name="Notification-Web"', $phpunit);
    }

    public function test_full_cycle_restores_exact_phpunit_xml(): void
    {
        $this->artisan('remove:module', ['name' => 'Dummy', '--force' => true])
            ->assertSuccessful();

        $phpunitAfter = file_get_contents(base_path('phpunit.xml'));

        $this->assertSame($this->phpunitBackup, $phpunitAfter);
    }

    // == HELPERS ==

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
