<?php

namespace Modules\Permission\Listeners;

use Modules\Permission\Events\RoleAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogRoleChange implements ShouldQueue
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
    public function handle(RoleAssigned $event): void
    {
        logger()->info("Role assigned to user", [
            'user_ulid'  => $event->userUlid,
            'user_email' => $event->userEmail,
            'role_name'  => $event->roleName,
            'timestamp'  => now()->toDateTimeString(),
        ]);
    }
}
