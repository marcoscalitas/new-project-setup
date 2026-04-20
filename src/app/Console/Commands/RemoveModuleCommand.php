<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RemoveModuleCommand extends Command
{
    protected $signature = 'remove:module {name : The module name (e.g. Product)} {--force : Skip confirmation prompt}';

    protected $description = 'Remove a module and unregister its provider';

    private string $module;
    private string $modulePath;

    public function handle(): int
    {
        $this->module = Str::studly($this->argument('name'));
        $this->modulePath = base_path("modules/{$this->module}");

        if (!is_dir($this->modulePath)) {
            $this->error("Module [{$this->module}] does not exist!");
            return self::FAILURE;
        }

        $protected = ['Auth', 'User', 'Permission', 'Notification'];
        if (in_array($this->module, $protected)) {
            $this->error("Module [{$this->module}] is a core module and cannot be removed.");
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm("This will permanently delete modules/{$this->module}/ and all its files. Continue?")) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        $this->unregisterProvider();
        $this->unregisterTestSuite();
        $this->deleteDirectory($this->modulePath);

        $this->components->info("Module [{$this->module}] removed successfully.");

        $this->components->bulletList([
            "Remove event bindings from <comment>app/Providers/EventServiceProvider.php</comment> manually",
            "Remove seeder calls from <comment>database/seeders/DatabaseSeeder.php</comment> if needed",
            "Run <comment>composer dump-autoload</comment> to update autoloading",
        ]);

        return self::SUCCESS;
    }

    private function unregisterProvider(): void
    {
        $providersPath = base_path('bootstrap/providers.php');
        $content = file_get_contents($providersPath);

        $escaped = preg_quote("Modules\\{$this->module}\\Providers\\{$this->module}ServiceProvider::class,", '/');
        $pattern = '/\n\s*' . $escaped . '/';

        if (!preg_match($pattern, $content)) {
            return;
        }

        $content = preg_replace($pattern, '', $content);

        file_put_contents($providersPath, $content);
    }

    private function unregisterTestSuite(): void
    {
        $phpunitPath = base_path('phpunit.xml');
        $content = file_get_contents($phpunitPath);

        $escaped = preg_quote($this->module, '/');

        foreach (['-Api', '-Web'] as $suffix) {
            $pattern = '/\n\s*<testsuite name="' . $escaped . $suffix . '">\s*<directory>[^<]*<\/directory>\s*<\/testsuite>/s';
            $content = preg_replace($pattern, '', $content);
        }

        // Legacy: remove single suite without suffix (for older modules)
        $pattern = '/\n\s*<testsuite name="' . $escaped . '">\s*<directory>[^<]*<\/directory>\s*<\/testsuite>/s';
        $content = preg_replace($pattern, '', $content);

        file_put_contents($phpunitPath, $content);
    }

    private function deleteDirectory(string $path): void
    {
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
