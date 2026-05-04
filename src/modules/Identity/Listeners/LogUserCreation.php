<?php

namespace Modules\Identity\Listeners;

use Modules\User\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserCreation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        logger()->info("User created", [
            'user_ulid'  => $event->userUlid,
            'user_email' => $event->userEmail,
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }
}
