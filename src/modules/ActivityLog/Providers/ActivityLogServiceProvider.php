<?php

namespace Modules\ActivityLog\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\ActivityLog\Models\ActivityLog;
use Modules\ActivityLog\Policies\ActivityLogPolicy;
use Modules\ActivityLog\Services\ActivityLogExportService;
use Modules\ActivityLog\Services\SpatieActivityLogger;
use Shared\Contracts\ActivityLog\ActivityLogger;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('export.activity_log', ActivityLogExportService::class);
        $this->app->bind(ActivityLogger::class, SpatieActivityLogger::class);
    }

    public function boot(): void
    {
        if (is_dir($migrations = __DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }

        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);

        if (file_exists($api = __DIR__.'/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
    }
}
