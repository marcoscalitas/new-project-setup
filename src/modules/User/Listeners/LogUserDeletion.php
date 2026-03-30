<?php

namespace Modules\User\Listeners;

use Modules\User\Events\UserDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserDeletion implements ShouldQueue
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
    public function handle(UserDeleted $event): void
    {
        logger()->info("User deleted", [
            'user_id'    => $event->userId,
            'user_email' => $event->userEmail,
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }
}
