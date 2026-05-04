<?php

namespace Modules\Notification\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\NotificationSenderInterface;
use Modules\Notification\Services\NotificationDispatcher;
use Modules\User\Events\UserCreated;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Modules\Notification\Listeners\LogNotificationDeletion;
use Modules\Notification\Listeners\LogNotificationRead;
use Modules\Notification\Listeners\NotifyOnPermissionCreated;
use Modules\Notification\Listeners\NotifyOnPermissionDeleted;
use Modules\Notification\Listeners\NotifyOnRoleCreated;
use Modules\Notification\Listeners\NotifyOnRoleDeleted;
use Modules\Notification\Listeners\NotifyOnUserCreated;
use Modules\Notification\Listeners\NotifyOnUserDeleted;
use Modules\Notification\Listeners\NotifyOnUserUpdated;
use Modules\Permission\Events\PermissionCreated;
use Modules\Permission\Events\PermissionDeleted;
use Modules\Permission\Events\RoleCreated;
use Modules\Permission\Events\RoleDeleted;
use Modules\User\Events\UserDeleted;
use Modules\User\Events\UserUpdated;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(NotificationSenderInterface::class, NotificationDispatcher::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(NotificationRead::class, [LogNotificationRead::class, 'handle']);
        Event::listen(NotificationDeleted::class, [LogNotificationDeletion::class, 'handle']);

        Event::listen(UserCreated::class, [NotifyOnUserCreated::class, 'handle']);
        Event::listen(UserUpdated::class, [NotifyOnUserUpdated::class, 'handle']);
        Event::listen(UserDeleted::class, [NotifyOnUserDeleted::class, 'handle']);
        Event::listen(RoleCreated::class, [NotifyOnRoleCreated::class, 'handle']);
        Event::listen(RoleDeleted::class, [NotifyOnRoleDeleted::class, 'handle']);
        Event::listen(PermissionCreated::class, [NotifyOnPermissionCreated::class, 'handle']);
        Event::listen(PermissionDeleted::class, [NotifyOnPermissionDeleted::class, 'handle']);

        if (file_exists($api = __DIR__ . '/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
        if (file_exists($web = __DIR__ . '/../Routes/web.php')) {
            Route::middleware('web')->group($web);
        }
        if (is_dir($migrations = __DIR__ . '/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }
    }
}
