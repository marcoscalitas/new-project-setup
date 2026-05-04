<?php

namespace Modules\Authorization\Listeners;

use Modules\Authorization\Events\RoleCreated;
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
            'role_ulid' => $event->roleUlid,
            'role_name' => $event->roleName,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
