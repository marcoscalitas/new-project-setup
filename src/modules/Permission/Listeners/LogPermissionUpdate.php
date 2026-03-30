<?php

namespace Modules\Permission\Listeners;

use Modules\Permission\Events\PermissionUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogPermissionUpdate implements ShouldQueue
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
    public function handle(PermissionUpdated $event): void
    {
        logger()->info("Permission updated", [
            'permission_name' => $event->permissionName,
            'old_name'        => $event->oldName,
            'timestamp'       => now()->toDateTimeString(),
        ]);
    }
}
