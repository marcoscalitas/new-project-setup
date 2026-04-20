<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\ActivityNotification;
use Modules\User\Events\UserUpdated;
use Modules\User\Models\User;

class NotifyOnUserUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserUpdated $event): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->where('id', '!=', $event->user->id)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new ActivityNotification(
                type: 'user_updated',
                message: "User updated: {$event->user->email}",
                data: ['user_id' => $event->user->id, 'email' => $event->user->email],
            ));
        }
    }
}
