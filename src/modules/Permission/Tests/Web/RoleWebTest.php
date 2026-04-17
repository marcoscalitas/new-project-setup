<?php

namespace Modules\Permission\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Permission\Models\Permission;
use Modules\Permission\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

class RoleWebTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->grantPermissions();
    }

    private function grantPermissions(): void
    {
        $perms = [];
        foreach (['role.list', 'role.view', 'role.create', 'role.update', 'role.delete'] as $name) {
            $perms[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $this->user->givePermissionTo($perms);
    }

    // == LIST ==

    public function test_authenticated_user_can_list_roles(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->getJson('/roles');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_unauthenticated_user_cannot_list_roles(): void
    {
        $response = $this->getJson('/roles');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_role(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/roles', ['name' => 'admin']);

        $response->assertCreated()
            ->assertJsonPath('name', 'admin');

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }

    public function test_create_role_with_permissions(): void
    {
        $p1 = Permission::create(['name' => 'user.list', 'guard_name' => 'web']);
        $p2 = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->postJson('/roles', [
                'name'        => 'admin',
                'permissions' => [$p1->name, $p2->name],
            ]);

        $response->assertCreated()
            ->assertJsonCount(2, 'permissions');
    }

    public function test_create_role_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/roles', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // == SHOW ==

    public function test_user_can_view_role(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->getJson("/roles/{$role->id}");

        $response->assertOk()
            ->assertJsonPath('name', 'admin');
    }

    // == UPDATE ==

    public function test_user_can_update_role(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->putJson("/roles/{$role->id}", ['name' => 'super-admin']);

        $response->assertOk()
            ->assertJsonPath('name', 'super-admin');

        $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
    }

    // == DESTROY ==

    public function test_user_can_delete_role(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->deleteJson("/roles/{$role->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
