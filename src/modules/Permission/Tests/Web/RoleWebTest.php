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

    // == VIEWS ==

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
            ->get("/roles/{$role->ulid}");

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
            ->get("/roles/{$role->ulid}/edit");

        $response->assertOk()
            ->assertViewIs('permission::roles.edit')
            ->assertViewHas(['role', 'permissions']);
    }

    // == MUTATIONS ==

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
            ->put("/roles/{$role->ulid}", ['name' => 'super-admin']);

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');
    }

    public function test_destroy_redirects_for_browser(): void
    {
        $role = Role::create(['name' => 'temp', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->delete("/roles/{$role->ulid}");

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');
    }

    // == AUTH ==

    public function test_unauthenticated_browser_is_redirected_to_login(): void
    {
        $response = $this->get('/roles');

        $response->assertRedirect('/auth/login');
    }

    // == BUSINESS RULES ==

    public function test_cannot_delete_admin_role_via_browser(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->delete("/roles/{$role->ulid}");

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'deleted_at' => null]);
    }

    public function test_update_with_empty_permissions_removes_all(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $perm = Permission::create(['name' => 'test.perm', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $response = $this->actingAs($this->user)
            ->put("/roles/{$role->ulid}", [
                'name'        => 'editor',
                'permissions' => '',
            ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertCount(0, $role->fresh()->permissions);
    }
}
