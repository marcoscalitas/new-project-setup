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
        //
    }

    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityLogPolicy::class);

        Route::prefix('api/v1')->middleware('api')->group(__DIR__ . '/../Routes/api.php');
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'activitylog');
    }
}
