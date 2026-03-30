<?php

namespace Modules\User\Events;

use Modules\User\Models\User;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDeleted
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $userId, public string $userEmail)
    {
    }

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
