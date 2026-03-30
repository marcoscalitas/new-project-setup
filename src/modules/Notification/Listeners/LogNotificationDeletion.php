<?php

namespace Modules\Notification\Listeners;

use Modules\Notification\Events\NotificationDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogNotificationDeletion implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationDeleted $event): void
    {
        logger()->info("Notification deleted", [
            'user_id'         => $event->userId,
            'notification_id' => $event->notificationId,
            'timestamp'       => now()->toDateTimeString(),
        ]);
    }
}
