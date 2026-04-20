<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\Permission\Events\RoleCreated;
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
                message: "New role created: {$event->role->name}",
                data: ['role_id' => $event->role->id, 'role_name' => $event->role->name],
            ));
        }
    }
}
