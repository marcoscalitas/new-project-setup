<?php

namespace Modules\Export\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\ActivityLog\Services\ActivityLogExportService;
use Modules\Export\Commands\PurgeExpiredExportsCommand;
use Modules\Export\Contracts\ExportableInterface;
use Modules\User\Services\UserExportService;

class ExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../../config/export.php', 'export');

        $this->app->bind('export.users', UserExportService::class);
        $this->app->bind('export.activity_log', ActivityLogExportService::class);
    }

    public function boot(): void
    {
        Route::prefix('api/v1')->middleware('api')->group(__DIR__ . '/../Routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->commands([PurgeExpiredExportsCommand::class]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('exports:purge')->daily();
        });
    }
}
