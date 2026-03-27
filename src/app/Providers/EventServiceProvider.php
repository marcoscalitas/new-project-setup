<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Auth\Events\UserCreated;
use Modules\Auth\Listeners\SendWelcomeEmail;
use Modules\Auth\Listeners\LogUserCreation;
use Modules\Permission\Events\RoleAssigned;
use Modules\Permission\Listeners\LogRoleChange;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserCreated::class => [
            SendWelcomeEmail::class,
            LogUserCreation::class,
        ],
        RoleAssigned::class => [
            LogRoleChange::class,
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
