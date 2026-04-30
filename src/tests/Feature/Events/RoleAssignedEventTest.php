<?php

namespace Tests\Feature\Events;

use Illuminate\Support\Facades\Event;
use Modules\Permission\Events\RoleAssigned;
use Modules\User\Models\User;
use Modules\Permission\Models\Role;
use Tests\TestCase;

class RoleAssignedEventTest extends TestCase
{
    public function test_role_assigned_event_can_be_dispatched(): void
    {
        Event::fake([RoleAssigned::class]);

        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        RoleAssigned::dispatch($user->ulid, $user->email, $role->name);

        Event::assertDispatched(RoleAssigned::class);
    }

    public function test_role_assigned_event_contains_user_and_role(): void
    {
        Event::fake([RoleAssigned::class]);

        $user = User::factory()->create();
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        RoleAssigned::dispatch($user->ulid, $user->email, $role->name);

        Event::assertDispatched(RoleAssigned::class, function ($event) use ($user, $role) {
            return $event->userUlid === $user->ulid && $event->roleName === $role->name;
        });
    }

    public function test_role_assigned_event_is_dispatchable(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);

        $event = new RoleAssigned($user->ulid, $user->email, $role->name);

        $this->assertEquals($user->ulid, $event->userUlid);
        $this->assertEquals($user->email, $event->userEmail);
        $this->assertEquals($role->name, $event->roleName);
    }
}
