<?php

namespace Modules\Notification\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Modules\Notification\Listeners\LogNotificationDeletion;
use Modules\Notification\Listeners\LogNotificationRead;

class NotificationServiceProvider extends ServiceProvider
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
        Event::listen(NotificationRead::class, [LogNotificationRead::class, 'handle']);
        Event::listen(NotificationDeleted::class, [LogNotificationDeletion::class, 'handle']);

        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        Route::prefix('api')->middleware('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
