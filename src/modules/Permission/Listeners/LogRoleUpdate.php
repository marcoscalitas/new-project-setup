<?php

namespace Modules\Permission\Listeners;

use Modules\Permission\Events\RoleUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogRoleUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
    }

    public function handle(RoleUpdated $event): void
    {
        logger()->info("Role updated", [
            'role_name' => $event->roleName,
            'old_name'  => $event->oldName,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
