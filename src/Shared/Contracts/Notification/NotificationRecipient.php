<?php

namespace Shared\Contracts\Notification;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface NotificationRecipient
{
    public function notifications(): MorphMany;

    public function unreadNotifications(): MorphMany;

    public function notificationRecipientId(): string|int;
}
