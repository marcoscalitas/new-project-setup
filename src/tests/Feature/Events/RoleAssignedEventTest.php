<?php

namespace Tests\Feature\Events;

use Illuminate\Support\Facades\Event;
use Modules\Permission\Events\RoleAssigned;
use Modules\User\Models\User;
use Modules\Permission\Models\Role;
use Tests\TestCase;

class RoleAssignedEventTest extends TestCase
{
    public function test_role_assigned_event_can_be_dispatched()
    {
        Event::fake();

        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        RoleAssigned::dispatch($user, $role);

        Event::assertDispatched(RoleAssigned::class);
    }

    public function test_role_assigned_event_contains_user_and_role()
    {
        Event::fake();

        $user = User::factory()->create();
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        RoleAssigned::dispatch($user, $role);

        Event::assertDispatched(RoleAssigned::class, function ($event) use ($user, $role) {
            return $event->user->id === $user->id && $event->role->id === $role->id;
        });
    }

    public function test_role_assigned_event_is_dispatchable()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);

        // Just verify we can create and dispatch the event
        $event = new RoleAssigned($user, $role);

        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($role->id, $event->role->id);
    }
}
