<?php

namespace Modules\Permission\Listeners;

use Modules\Permission\Events\RoleCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogRoleCreation implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
    }

    public function handle(RoleCreated $event): void
    {
        logger()->info("Role created", [
            'role_id'   => $event->role->id,
            'role_name' => $event->role->name,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
