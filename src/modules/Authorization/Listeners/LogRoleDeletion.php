<?php

namespace Modules\Authorization\Listeners;

use Modules\Authorization\Events\RoleDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogRoleDeletion implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
    }

    public function handle(RoleDeleted $event): void
    {
        logger()->info("Role deleted", [
            'role_id'   => $event->roleId,
            'role_name' => $event->roleName,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
