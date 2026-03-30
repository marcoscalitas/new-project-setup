<?php

namespace Modules\Permission\Listeners;

use Modules\Permission\Events\PermissionDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogPermissionDeletion implements ShouldQueue
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
    public function handle(PermissionDeleted $event): void
    {
        logger()->info("Permission deleted", [
            'permission_id'   => $event->permissionId,
            'permission_name' => $event->permissionName,
            'timestamp'       => now()->toDateTimeString(),
        ]);
    }
}
