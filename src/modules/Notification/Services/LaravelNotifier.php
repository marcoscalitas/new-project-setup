<?php

namespace Modules\Notification\Services;

use Modules\Notification\Notifications\ActivityNotification;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class LaravelNotifier implements Notifier
{
    public function send(mixed $notifiable, NotificationData $data): void
    {
        $notifiable->notify($this->toLaravelNotification($data));
    }

    public function sendNow(mixed $notifiable, NotificationData $data): void
    {
        $notifiable->notifyNow($this->toLaravelNotification($data));
    }

    private function toLaravelNotification(NotificationData $data): ActivityNotification
    {
        return new ActivityNotification(
            type: $data->type,
            message: $data->message,
            data: $data->data,
            channels: $data->channels,
        );
    }
}
