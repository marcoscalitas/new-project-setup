<?php

namespace Modules\ActivityLog\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\ActivityLog\Policies\ActivityLogPolicy;
use Spatie\Activitylog\Models\Activity;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('export.activity_log', \Modules\ActivityLog\Services\ActivityLogExportService::class);
    }

    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityLogPolicy::class);

        if (file_exists($api = __DIR__ . '/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
    }
}
