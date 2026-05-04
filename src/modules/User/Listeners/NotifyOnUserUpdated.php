<?php

namespace Modules\User\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\User\Events\UserUpdated;
use Modules\User\Models\User;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class NotifyOnUserUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private Notifier $notifier) {}

    public function handle(UserUpdated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->where('ulid', '!=', $event->userUlid)
            ->get();

        foreach ($admins as $admin) {
            $this->notifier->send($admin, new NotificationData(
                type: 'user_updated',
                message: "User updated: {$event->userEmail}",
                data: ['user_ulid' => $event->userUlid, 'email' => $event->userEmail, 'url' => route('users.show', $event->userUlid)],
            ));
        }
    }
}
