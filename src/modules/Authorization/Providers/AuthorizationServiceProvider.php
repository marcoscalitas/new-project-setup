<?php

namespace Modules\Authorization\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Authorization\Events\PermissionCreated;
use Modules\Authorization\Events\PermissionDeleted;
use Modules\Authorization\Events\PermissionUpdated;
use Modules\Authorization\Events\RoleAssigned;
use Modules\Authorization\Events\RoleCreated;
use Modules\Authorization\Events\RoleDeleted;
use Modules\Authorization\Events\RoleUpdated;
use Modules\Authorization\Listeners\LogPermissionCreation;
use Modules\Authorization\Listeners\LogPermissionDeletion;
use Modules\Authorization\Listeners\LogPermissionUpdate;
use Modules\Authorization\Listeners\LogRoleChange;
use Modules\Authorization\Listeners\LogRoleCreation;
use Modules\Authorization\Listeners\LogRoleDeletion;
use Modules\Authorization\Listeners\LogRoleUpdate;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Modules\Authorization\Policies\PermissionPolicy;
use Modules\Authorization\Policies\RolePolicy;

class AuthorizationServiceProvider extends ServiceProvider
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
        Event::listen(RoleCreated::class, [LogRoleCreation::class, 'handle']);
        Event::listen(RoleUpdated::class, [LogRoleUpdate::class, 'handle']);
        Event::listen(RoleDeleted::class, [LogRoleDeletion::class, 'handle']);
        Event::listen(PermissionCreated::class, [LogPermissionCreation::class, 'handle']);
        Event::listen(PermissionUpdated::class, [LogPermissionUpdate::class, 'handle']);
        Event::listen(PermissionDeleted::class, [LogPermissionDeletion::class, 'handle']);

        if (file_exists($web = __DIR__ . '/../Routes/web.php')) {
            Route::middleware('web')->group($web);
        }
        if (file_exists($api = __DIR__ . '/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
        if (is_dir($migrations = __DIR__ . '/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }
        if (is_dir($views = __DIR__ . '/../Resources/views')) {
            $this->loadViewsFrom($views, 'authorization');
        }
    }
}
