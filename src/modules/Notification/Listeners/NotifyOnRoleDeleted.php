<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\Permission\Events\RoleDeleted;
use Modules\User\Models\User;

class NotifyOnRoleDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RoleDeleted $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $admin->notify(new ActivityNotification(
                type: 'role_deleted',
                message: "Role deleted: {$event->roleName}",
                data: ['role_id' => $event->roleId, 'role_name' => $event->roleName],
            ));
        }
    }
}
