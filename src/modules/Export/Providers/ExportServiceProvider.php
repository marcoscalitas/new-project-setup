<?php

namespace Modules\Export\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Export\Commands\PurgeExpiredExportsCommand;

class ExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../../config/export.php', 'export');
    }

    public function boot(): void
    {
        if (file_exists($api = __DIR__ . '/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }

        if (is_dir($migrations = __DIR__ . '/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }

        $this->commands([PurgeExpiredExportsCommand::class]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('exports:purge')->daily();
        });
    }
}
