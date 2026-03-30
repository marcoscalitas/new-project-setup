<?php

namespace Modules\User\Listeners;

use Modules\User\Events\UserUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserUpdate implements ShouldQueue
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
    public function handle(UserUpdated $event): void
    {
        logger()->info("User updated", [
            'user_id'    => $event->user->id,
            'user_email' => $event->user->email,
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }
}
