<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\Permission\Events\PermissionCreated;
use Modules\User\Models\User;

class NotifyOnPermissionCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PermissionCreated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $admin->notify(new ActivityNotification(
                type: 'permission_created',
                message: "New permission created: {$event->permission->name}",
                data: ['permission_id' => $event->permission->id, 'permission_name' => $event->permission->name, 'url' => route('permissions.show', $event->permission->ulid)],
            ));
        }
    }
}
