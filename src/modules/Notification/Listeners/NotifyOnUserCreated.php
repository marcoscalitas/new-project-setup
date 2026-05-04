<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Auth\Events\UserCreated;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\User\Models\User;

class NotifyOnUserCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserCreated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->where('ulid', '!=', $event->userUlid)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new ActivityNotification(
                type: 'user_created',
                message: "New user registered: {$event->userEmail}",
                data: ['user_ulid' => $event->userUlid, 'email' => $event->userEmail, 'url' => route('users.show', $event->userUlid)],
            ));
        }
    }
}
