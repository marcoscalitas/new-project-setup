<?php

namespace Modules\Permission\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleDeleted
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(public int $roleId, public string $roleName)
    {
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
