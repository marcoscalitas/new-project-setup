<?php

namespace Modules\Notification\Actions;

use Modules\User\Models\User;

class MarkAllNotificationsAsRead
{
    public function execute(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
