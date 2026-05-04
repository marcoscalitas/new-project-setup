<?php

namespace Modules\Authorization\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Authorization\Events\RoleDeleted;
use Modules\User\Models\User;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class NotifyOnRoleDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private Notifier $notifier) {}

    public function handle(RoleDeleted $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $this->notifier->send($admin, new NotificationData(
                type: 'role_deleted',
                message: "Role deleted: {$event->roleName}",
                data: ['role_id' => $event->roleId, 'role_name' => $event->roleName, 'url' => route('roles.trashed')],
            ));
        }
    }
}
