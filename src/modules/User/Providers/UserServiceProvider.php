<?php

namespace Modules\User\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\User\Events\UserCreated;
use Modules\User\Events\UserDeleted;
use Modules\User\Events\UserUpdated;
use Modules\User\Listeners\LogUserDeletion;
use Modules\User\Listeners\LogUserUpdate;
use Modules\User\Listeners\NotifyOnUserCreated;
use Modules\User\Listeners\NotifyOnUserDeleted;
use Modules\User\Listeners\NotifyOnUserUpdated;
use Modules\User\Models\User;
use Modules\User\Policies\UserPolicy;
use Modules\User\Services\UserExportService;
use Shared\Contracts\Export\ExportRegistry;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserExportService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        app(ExportRegistry::class)->register(app(UserExportService::class));

        Event::listen(UserUpdated::class, [LogUserUpdate::class, 'handle']);
        Event::listen(UserDeleted::class, [LogUserDeletion::class, 'handle']);
        Event::listen(UserCreated::class, [NotifyOnUserCreated::class, 'handle']);
        Event::listen(UserUpdated::class, [NotifyOnUserUpdated::class, 'handle']);
        Event::listen(UserDeleted::class, [NotifyOnUserDeleted::class, 'handle']);

        if (file_exists($web = __DIR__.'/../Routes/web.php')) {
            Route::middleware('web')->group($web);
        }
        if (file_exists($api = __DIR__.'/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
        if (is_dir($migrations = __DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }
        if (is_dir($views = __DIR__.'/../Resources/views')) {
            $this->loadViewsFrom($views, 'user');
        }
    }
}
