<?php

namespace Modules\User\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

class UserWebTest extends TestCase
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
        foreach (['user.list', 'user.view', 'user.create', 'user.update', 'user.delete'] as $name) {
            $perms[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $this->user->givePermissionTo($perms);
    }

    // == VIEWS ==

    public function test_index_returns_blade_view_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/users');

        $response->assertOk()
            ->assertViewIs('user::users.index')
            ->assertViewHas('users');
    }

    public function test_index_respects_page_length_for_browser(): void
    {
        User::factory()->count(12)->create();

        $response = $this->actingAs($this->user)
            ->get('/users?per_page=5');

        $response->assertOk()
            ->assertSee(__('ui.rows_per_page'));

        $this->assertSame(5, $response->viewData('users')->perPage());
    }

    public function test_index_falls_back_to_default_page_length_for_browser(): void
    {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->user)
            ->get('/users?per_page=999');

        $response->assertOk();

        $this->assertSame(15, $response->viewData('users')->perPage());
    }

    public function test_show_returns_blade_view_for_browser(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/users/{$target->ulid}");

        $response->assertOk()
            ->assertViewIs('user::users.show')
            ->assertViewHas('user');
    }

    public function test_create_returns_blade_view(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/users/create');

        $response->assertOk()
            ->assertViewIs('user::users.create')
            ->assertViewHas('roles');
    }

    public function test_edit_returns_blade_view(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/users/{$target->ulid}/edit");

        $response->assertOk()
            ->assertViewIs('user::users.edit')
            ->assertViewHas(['user', 'roles']);
    }

    // == MUTATIONS ==

    public function test_store_redirects_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/users', [
                'name' => 'Browser User',
                'email' => 'browser@example.com',
                'password' => 'SecurePass1!',
                'password_confirmation' => 'SecurePass1!',
            ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'browser@example.com']);
    }

    public function test_update_redirects_for_browser(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->put("/users/{$target->ulid}", [
                'name' => 'Updated Browser',
            ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');
    }

    public function test_destroy_redirects_for_browser(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/users/{$target->ulid}");

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    // == AUTH ==

    public function test_unauthenticated_browser_is_redirected_to_login(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/auth/login');
    }

    // == BUSINESS RULES ==

    public function test_update_with_empty_roles_removes_all(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $target = User::factory()->create();
        $target->assignRole($role);

        $response = $this->actingAs($this->user)
            ->put("/users/{$target->ulid}", [
                'name' => $target->name,
                'roles' => '',
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertCount(0, $target->fresh()->roles);
    }

    public function test_cannot_remove_admin_role_from_last_admin_via_browser(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $response = $this->actingAs($this->user)
            ->put("/users/{$admin->ulid}", [
                'name' => $admin->name,
                'roles' => '',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_cannot_delete_last_admin_via_browser(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $response = $this->actingAs($this->user)
            ->delete("/users/{$admin->ulid}");

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }
}
