<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\Authorization\Events\RoleCreated;
use Modules\User\Models\User;

class NotifyOnRoleCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RoleCreated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $admin->notify(new ActivityNotification(
                type: 'role_created',
                message: "New role created: {$event->roleName}",
                data: ['role_ulid' => $event->roleUlid, 'role_name' => $event->roleName, 'url' => route('roles.show', $event->roleUlid)],
            ));
        }
    }
}
