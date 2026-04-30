<?php

namespace Modules\Notification\Listeners;

use Modules\Notification\Events\NotificationRead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogNotificationRead implements ShouldQueue
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
    public function handle(NotificationRead $event): void
    {
        logger()->info("Notification marked as read", [
            'user_ulid'       => $event->userUlid,
            'notification_id' => $event->notificationId,
            'timestamp'       => now()->toDateTimeString(),
        ]);
    }
}
