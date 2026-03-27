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
            'user_id' => $event->user->id,
            'user_email' => $event->user->email,
            'role_id' => $event->role->id,
            'role_name' => $event->role->name,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
