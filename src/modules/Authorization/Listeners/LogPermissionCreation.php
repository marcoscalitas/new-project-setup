<?php

namespace Modules\Authorization\Listeners;

use Modules\Authorization\Events\PermissionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogPermissionCreation implements ShouldQueue
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
    public function handle(PermissionCreated $event): void
    {
        logger()->info("Permission created", [
            'permission_id'   => $event->permission->id,
            'permission_name' => $event->permission->name,
            'timestamp'       => now()->toDateTimeString(),
        ]);
    }
}
