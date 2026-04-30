<?php

namespace Modules\Permission\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleCreated
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(
        public string $roleUlid,
        public string $roleName,
    ) {}

    public function broadcastOn(): array
    {
        return [];
    }
}
