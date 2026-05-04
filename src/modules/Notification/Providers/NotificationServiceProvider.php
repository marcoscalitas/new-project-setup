<?php

namespace Modules\Notification\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Modules\Notification\Listeners\LogNotificationDeletion;
use Modules\Notification\Listeners\LogNotificationRead;
use Modules\Notification\Models\Notification;
use Modules\Notification\Services\LaravelNotifier;
use Shared\Contracts\Notification\Notifier;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app['config']->set('notifications.database_model', Notification::class);

        $this->app->bind(Notifier::class, LaravelNotifier::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(NotificationRead::class, [LogNotificationRead::class, 'handle']);
        Event::listen(NotificationDeleted::class, [LogNotificationDeletion::class, 'handle']);

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
