<?php

namespace Modules\Permission\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Permission\Events\PermissionCreated;
use Modules\Permission\Events\PermissionDeleted;
use Modules\Permission\Events\PermissionUpdated;
use Modules\Permission\Events\RoleAssigned;
use Modules\Permission\Listeners\LogPermissionCreation;
use Modules\Permission\Listeners\LogPermissionDeletion;
use Modules\Permission\Listeners\LogPermissionUpdate;
use Modules\Permission\Listeners\LogRoleChange;
use Modules\Permission\Models\Permission;
use Modules\Permission\Models\Role;
use Modules\Permission\Policies\PermissionPolicy;
use Modules\Permission\Policies\RolePolicy;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        Event::listen(RoleAssigned::class, [LogRoleChange::class, 'handle']);
        Event::listen(PermissionCreated::class, [LogPermissionCreation::class, 'handle']);
        Event::listen(PermissionUpdated::class, [LogPermissionUpdate::class, 'handle']);
        Event::listen(PermissionDeleted::class, [LogPermissionDeletion::class, 'handle']);

        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        Route::prefix('api')->middleware('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
