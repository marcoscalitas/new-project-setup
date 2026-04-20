<?php

namespace Modules\Permission\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Permission\Models\Role;

class RoleCreated
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(public Role $role)
    {
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
