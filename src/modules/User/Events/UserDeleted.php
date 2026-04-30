<?php

namespace Modules\User\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDeleted
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(
        public string $userUlid,
        public string $userEmail,
    ) {}

    public function broadcastOn(): array
    {
        return [];
    }
}
