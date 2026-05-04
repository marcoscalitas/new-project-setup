<?php

namespace Shared\Contracts\Notification;

use Shared\Data\Notification\NotificationData;

interface Notifier
{
    public function send(mixed $notifiable, NotificationData $data): void;

    public function sendNow(mixed $notifiable, NotificationData $data): void;
}
