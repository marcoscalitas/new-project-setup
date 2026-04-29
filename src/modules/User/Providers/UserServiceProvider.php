<?php

namespace Modules\User\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\User\Events\UserDeleted;
use Modules\User\Events\UserUpdated;
use Modules\User\Listeners\LogUserDeletion;
use Modules\User\Listeners\LogUserUpdate;
use Modules\User\Models\User;
use Modules\User\Policies\UserPolicy;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('export.users', \Modules\User\Services\UserExportService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        Event::listen(UserUpdated::class, [LogUserUpdate::class, 'handle']);
        Event::listen(UserDeleted::class, [LogUserDeletion::class, 'handle']);

        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        Route::prefix('api/v1')->middleware('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'user');
    }
}

