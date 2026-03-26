<?php

namespace Modules\Notification\Policies;

use Illuminate\Notifications\DatabaseNotification;
use Modules\User\Models\User;

class NotificationPolicy
{
    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $user->id === $notification->notifiable_id;
    }

    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return $user->id === $notification->notifiable_id;
    }
}
