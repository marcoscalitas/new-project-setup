<?php

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\UserCreated;
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
            'user_id' => $event->user->id,
            'user_email' => $event->user->email,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
