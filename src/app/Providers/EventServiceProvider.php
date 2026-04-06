<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Auth\Events\UserCreated;
use Modules\Auth\Listeners\SendWelcomeEmail;
use Modules\Auth\Listeners\LogUserCreation;
use Modules\Notification\Events\NotificationDeleted;
use Modules\Notification\Events\NotificationRead;
use Modules\Notification\Listeners\LogNotificationDeletion;
use Modules\Notification\Listeners\LogNotificationRead;
use Modules\Permission\Events\PermissionCreated;
use Modules\Permission\Events\PermissionDeleted;
use Modules\Permission\Events\PermissionUpdated;
use Modules\Permission\Events\RoleAssigned;
use Modules\Permission\Listeners\LogPermissionCreation;
use Modules\Permission\Listeners\LogPermissionDeletion;
use Modules\Permission\Listeners\LogPermissionUpdate;
use Modules\Permission\Listeners\LogRoleChange;
use Modules\User\Events\UserDeleted;
use Modules\User\Events\UserUpdated;
use Modules\User\Listeners\LogUserDeletion;
use Modules\User\Listeners\LogUserUpdate;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserCreated::class => [
            SendWelcomeEmail::class,
            LogUserCreation::class,
        ],
        UserUpdated::class => [
            LogUserUpdate::class,
        ],
        UserDeleted::class => [
            LogUserDeletion::class,
        ],
        RoleAssigned::class => [
            LogRoleChange::class,
        ],
        PermissionCreated::class => [
            LogPermissionCreation::class,
        ],
        PermissionUpdated::class => [
            LogPermissionUpdate::class,
        ],
        PermissionDeleted::class => [
            LogPermissionDeletion::class,
        ],
        NotificationRead::class => [
            LogNotificationRead::class,
        ],
        NotificationDeleted::class => [
            LogNotificationDeletion::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
