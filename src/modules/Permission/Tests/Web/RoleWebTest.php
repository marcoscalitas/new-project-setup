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

    // == BLADE VIEWS ==

    public function test_index_returns_blade_view_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/roles');

        $response->assertOk()
            ->assertViewIs('permission::roles.index')
            ->assertViewHas('roles');
    }

    public function test_show_returns_blade_view_for_browser(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->get("/roles/{$role->id}");

        $response->assertOk()
            ->assertViewIs('permission::roles.show')
            ->assertViewHas('role');
    }

    public function test_create_returns_blade_view(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/roles/create');

        $response->assertOk()
            ->assertViewIs('permission::roles.create')
            ->assertViewHas('permissions');
    }

    public function test_edit_returns_blade_view(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->get("/roles/{$role->id}/edit");

        $response->assertOk()
            ->assertViewIs('permission::roles.edit')
            ->assertViewHas(['role', 'permissions']);
    }

    public function test_store_redirects_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/roles', ['name' => 'manager']);

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'manager']);
    }

    public function test_update_redirects_for_browser(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->put("/roles/{$role->id}", ['name' => 'super-admin']);

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');
    }

    public function test_destroy_redirects_for_browser(): void
    {
        $role = Role::create(['name' => 'temp', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->delete("/roles/{$role->id}");

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');
    }

    public function test_unauthenticated_browser_is_redirected_to_login(): void
    {
        $response = $this->get('/roles');

        $response->assertRedirect('/auth/login');
    }

    public function test_update_with_empty_permissions_removes_all(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $perm = Permission::create(['name' => 'test.perm', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $response = $this->actingAs($this->user)
            ->put("/roles/{$role->id}", [
                'name'        => 'editor',
                'permissions' => '',
            ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertCount(0, $role->fresh()->permissions);
    }
}
