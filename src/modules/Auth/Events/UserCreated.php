<?php

namespace Modules\Auth\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreated
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(
        public string $userUlid,
        public string $userName,
        public string $userEmail,
    ) {}

    public function broadcastOn(): array
    {
        return [];
    }
}
