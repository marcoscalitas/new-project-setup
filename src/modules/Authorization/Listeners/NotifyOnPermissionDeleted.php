<?php

namespace Modules\Authorization\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Authorization\Events\PermissionDeleted;
use Modules\User\Models\User;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class NotifyOnPermissionDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private Notifier $notifier) {}

    public function handle(PermissionDeleted $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $this->notifier->send($admin, new NotificationData(
                type: 'permission_deleted',
                message: "Permission deleted: {$event->permissionName}",
                data: ['permission_id' => $event->permissionId, 'permission_name' => $event->permissionName, 'url' => route('permissions.trashed')],
            ));
        }
    }
}
