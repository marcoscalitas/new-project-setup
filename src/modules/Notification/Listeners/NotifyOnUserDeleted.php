<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\User\Events\UserDeleted;
use Modules\User\Models\User;

class NotifyOnUserDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserDeleted $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            $admin->notify(new ActivityNotification(
                type: 'user_deleted',
                message: "User deleted: {$event->userEmail}",
                data: ['user_ulid' => $event->userUlid, 'email' => $event->userEmail, 'url' => route('users.trashed')],
            ));
        }
    }
}
