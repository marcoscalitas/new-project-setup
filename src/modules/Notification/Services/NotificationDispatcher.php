<?php

namespace Modules\Notification\Services;

use Modules\Core\Contracts\NotificationSenderInterface;

class NotificationDispatcher implements NotificationSenderInterface
{
    public function send(mixed $notifiable, mixed $notification): void
    {
        $notifiable->notify($notification);
    }

    public function sendNow(mixed $notifiable, mixed $notification): void
    {
        $notifiable->notifyNow($notification);
    }
}
