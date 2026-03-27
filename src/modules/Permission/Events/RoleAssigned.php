<?php

namespace Modules\Permission\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Models\Role;
use Modules\User\Models\User;

class RoleAssigned
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user, public Role $role)
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
