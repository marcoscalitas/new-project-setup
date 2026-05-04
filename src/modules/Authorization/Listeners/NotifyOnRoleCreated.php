<?php

namespace Modules\Authorization\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Authorization\Events\RoleCreated;
use Modules\User\Models\User;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class NotifyOnRoleCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private Notifier $notifier) {}

    public function handle(RoleCreated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $this->notifier->send($admin, new NotificationData(
                type: 'role_created',
                message: "New role created: {$event->roleName}",
                data: ['role_ulid' => $event->roleUlid, 'role_name' => $event->roleName, 'url' => route('roles.show', $event->roleUlid)],
            ));
        }
    }
}
