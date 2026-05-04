<?php

namespace Modules\Authorization\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleUpdated
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(public string $roleName, public string $oldName)
    {
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
