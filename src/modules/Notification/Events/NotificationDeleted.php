<?php

namespace Modules\Notification\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationDeleted
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public string $userUlid, public string $notificationId) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
