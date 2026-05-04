<?php

namespace Modules\User\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\User\Events\UserCreated;
use Modules\User\Models\User;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class NotifyOnUserCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private Notifier $notifier) {}

    public function handle(UserCreated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->where('ulid', '!=', $event->userUlid)
            ->get();

        foreach ($admins as $admin) {
            $this->notifier->send($admin, new NotificationData(
                type: 'user_created',
                message: "New user registered: {$event->userEmail}",
                data: ['user_ulid' => $event->userUlid, 'email' => $event->userEmail, 'url' => route('users.show', $event->userUlid)],
            ));
        }
    }
}
