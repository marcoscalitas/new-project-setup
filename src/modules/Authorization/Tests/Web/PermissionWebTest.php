<?php

namespace Modules\Authorization\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authorization\Models\Permission;
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

    // == VIEWS ==

    public function test_index_returns_blade_view_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/permissions');

        $response->assertOk()
            ->assertViewIs('authorization::permissions.index')
            ->assertViewHas('permissions');
    }

    public function test_show_returns_blade_view_for_browser(): void
    {
        $permission = Permission::create(['name' => 'post.create', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->get("/permissions/{$permission->ulid}");

        $response->assertOk()
            ->assertViewIs('authorization::permissions.show')
            ->assertViewHas('permission');
    }

    public function test_create_returns_blade_view(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/permissions/create');

        $response->assertOk()
            ->assertViewIs('authorization::permissions.create');
    }

    public function test_edit_returns_blade_view(): void
    {
        $permission = Permission::create(['name' => 'post.create', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->get("/permissions/{$permission->ulid}/edit");

        $response->assertOk()
            ->assertViewIs('authorization::permissions.edit')
            ->assertViewHas('permission');
    }

    // == MUTATIONS ==

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
            ->put("/permissions/{$permission->ulid}", ['name' => 'post.edit']);

        $response->assertRedirect(route('permissions.index'))
            ->assertSessionHas('success');
    }

    public function test_destroy_redirects_for_browser(): void
    {
        $permission = Permission::create(['name' => 'temp.perm', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)
            ->delete("/permissions/{$permission->ulid}");

        $response->assertRedirect(route('permissions.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('permissions', ['id' => $permission->id]);
    }

    // == AUTH ==

    public function test_unauthenticated_browser_is_redirected_to_login(): void
    {
        $response = $this->get('/permissions');

        $response->assertRedirect('/auth/login');
    }

    public function test_user_without_permission_cannot_access_index(): void
    {
        $guest = User::factory()->create();

        $this->actingAs($guest)->get('/permissions')
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }
}
