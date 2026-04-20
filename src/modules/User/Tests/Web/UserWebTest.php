<?php

namespace Modules\User\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Permission\Models\Permission;
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

    // == LIST ==

    public function test_authenticated_user_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/users');

        $response->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/users');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_user(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/users', [
                'name'                  => 'John Doe',
                'email'                 => 'john@example.com',
                'password'              => 'SecurePass1!',
                'password_confirmation' => 'SecurePass1!',
            ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'John Doe')
            ->assertJsonPath('email', 'john@example.com');

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_create_user_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/users', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    // == SHOW ==

    public function test_user_can_view_user(): void
    {
        $target = User::factory()->create(['name' => 'Maria Silva']);

        $response = $this->actingAs($this->user)
            ->getJson("/users/{$target->id}");

        $response->assertOk()
            ->assertJsonPath('name', 'Maria Silva');
    }

    // == UPDATE ==

    public function test_user_can_update_user(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/users/{$target->id}", [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Name');

        $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
    }

    // == DESTROY ==

    public function test_user_can_delete_user(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/users/{$target->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    // == BLADE VIEWS ==

    public function test_index_returns_blade_view_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/users');

        $response->assertOk()
            ->assertViewIs('user::users.index')
            ->assertViewHas('users');
    }

    public function test_show_returns_blade_view_for_browser(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/users/{$target->id}");

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
            ->get("/users/{$target->id}/edit");

        $response->assertOk()
            ->assertViewIs('user::users.edit')
            ->assertViewHas(['user', 'roles']);
    }

    public function test_store_redirects_for_browser(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/users', [
                'name'                  => 'Browser User',
                'email'                 => 'browser@example.com',
                'password'              => 'SecurePass1!',
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
            ->put("/users/{$target->id}", [
                'name' => 'Updated Browser',
            ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');
    }

    public function test_destroy_redirects_for_browser(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/users/{$target->id}");

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_unauthenticated_browser_is_redirected_to_login(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/auth/login');
    }

    public function test_update_with_empty_roles_removes_all(): void
    {
        $role = \Modules\Permission\Models\Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $target = User::factory()->create();
        $target->assignRole($role);

        $response = $this->actingAs($this->user)
            ->put("/users/{$target->id}", [
                'name'  => $target->name,
                'roles' => '',
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertCount(0, $target->fresh()->roles);
    }

    public function test_cannot_remove_admin_role_from_last_admin_via_browser(): void
    {
        $adminRole = \Modules\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $response = $this->actingAs($this->user)
            ->put("/users/{$admin->id}", [
                'name'  => $admin->name,
                'roles' => '',
            ]);

        $response->assertSessionHasErrors(['roles']);
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_cannot_delete_last_admin_via_browser(): void
    {
        $adminRole = \Modules\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $response = $this->actingAs($this->user)
            ->delete("/users/{$admin->id}");

        $response->assertSessionHasErrors(['user']);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
