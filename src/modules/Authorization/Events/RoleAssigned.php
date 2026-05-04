<?php

namespace Modules\Authorization\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(
        public string $userUlid,
        public string $userEmail,
        public string $roleName,
    ) {}

    public function broadcastOn(): array
    {
        return [];
    }
}
