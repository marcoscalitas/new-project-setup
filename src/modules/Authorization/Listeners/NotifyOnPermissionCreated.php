<?php

namespace Modules\Authorization\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Authorization\Events\PermissionCreated;
use Modules\User\Models\User;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class NotifyOnPermissionCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private Notifier $notifier) {}

    public function handle(PermissionCreated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $this->notifier->send($admin, new NotificationData(
                type: 'permission_created',
                message: "New permission created: {$event->permission->name}",
                data: ['permission_id' => $event->permission->id, 'permission_name' => $event->permission->name, 'url' => route('permissions.show', $event->permission->ulid)],
            ));
        }
    }
}
