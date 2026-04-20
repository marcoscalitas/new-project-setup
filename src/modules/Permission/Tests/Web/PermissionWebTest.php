<?php

namespace Modules\Permission\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class PermissionWebTest extends TestCase
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
        foreach (['permission.list', 'permission.view', 'permission.create', 'permission.update', 'permission.delete'] as $name) {
            $perms[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $this->user->givePermissionTo($perms);
    }

    // == LIST ==

    public function test_authenticated_user_can_list_permissions(): void
    {
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'user.view', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->getJson('/permissions');

        $response->assertOk()
            ->assertJsonCount(7);
    }

    public function test_unauthenticated_user_cannot_list_permissions(): void
    {
        $response = $this->getJson('/permissions');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_permission(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/permissions', ['name' => 'user.list']);

        $response->assertCreated()
            ->assertJsonPath('name', 'user.list');

        $this->assertDatabaseHas('permissions', ['name' => 'user.list']);
    }

    public function test_create_permission_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/permissions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // == SHOW ==

    public function test_user_can_view_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->getJson("/permissions/{$permission->id}");

        $response->assertOk()
            ->assertJsonPath('name', 'user.list');
    }

    // == UPDATE ==

    public function test_user_can_update_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->putJson("/permissions/{$permission->id}", ['name' => 'user.view']);

        $response->assertOk()
            ->assertJsonPath('name', 'user.view');

        $this->assertDatabaseHas('permissions', ['name' => 'user.view']);
    }

    // == DESTROY ==

    public function test_user_can_delete_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->deleteJson("/permissions/{$permission->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('permissions', ['id' => $permission->id]);
    }

    // == BLADE VIEWS ==

    public function test_index_returns_blade_view_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/permissions');

        $response->assertOk()
            ->assertViewIs('permission::permissions.index')
            ->assertViewHas('permissions');
    }

    public function test_show_returns_blade_view_for_browser(): void
    {
        $permission = Permission::create(['name' => 'post.create', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->get("/permissions/{$permission->id}");

        $response->assertOk()
            ->assertViewIs('permission::permissions.show')
            ->assertViewHas('permission');
    }

    public function test_create_returns_blade_view(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/permissions/create');

        $response->assertOk()
            ->assertViewIs('permission::permissions.create');
    }

    public function test_edit_returns_blade_view(): void
    {
        $permission = Permission::create(['name' => 'post.create', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->get("/permissions/{$permission->id}/edit");

        $response->assertOk()
            ->assertViewIs('permission::permissions.edit')
            ->assertViewHas('permission');
    }

    public function test_store_redirects_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/permissions', ['name' => 'post.create']);

        $response->assertRedirect(route('permissions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('permissions', ['name' => 'post.create']);
    }

    public function test_update_redirects_for_browser(): void
    {
        $permission = Permission::create(['name' => 'post.create', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->put("/permissions/{$permission->id}", ['name' => 'post.edit']);

        $response->assertRedirect(route('permissions.index'))
            ->assertSessionHas('success');
    }

    public function test_destroy_redirects_for_browser(): void
    {
        $permission = Permission::create(['name' => 'temp.perm', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->delete("/permissions/{$permission->id}");

        $response->assertRedirect(route('permissions.index'))
            ->assertSessionHas('success');
    }

    public function test_unauthenticated_browser_is_redirected_to_login(): void
    {
        $response = $this->get('/permissions');

        $response->assertRedirect('/auth/login');
    }
}
