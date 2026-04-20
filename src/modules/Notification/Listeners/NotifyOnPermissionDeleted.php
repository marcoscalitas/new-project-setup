<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\Permission\Events\PermissionDeleted;
use Modules\User\Models\User;

class NotifyOnPermissionDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PermissionDeleted $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $admin->notify(new ActivityNotification(
                type: 'permission_deleted',
                message: "Permission deleted: {$event->permissionName}",
                data: ['permission_id' => $event->permissionId, 'permission_name' => $event->permissionName],
            ));
        }
    }
}
