<?php

namespace Modules\Authorization\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

class TrashedRoleWebTest extends TestCase
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

    // == TRASHED VIEW ==

    public function test_trashed_returns_blade_view(): void
    {
        $response = $this->actingAs($this->user)->get('/roles/trashed');

        $response->assertOk()
            ->assertViewIs('authorization::roles.trashed')
            ->assertViewHas('roles');
    }

    public function test_trashed_view_lists_only_deleted_roles(): void
    {
        Role::create(['name' => 'active-role', 'guard_name' => 'web']);
        $deleted = Role::create(['name' => 'deleted-role', 'guard_name' => 'web']);
        $deleted->delete();

        $response = $this->actingAs($this->user)->get('/roles/trashed');

        $roles = $response->viewData('roles');
        $names = collect($roles->items())->pluck('name')->all();
        $this->assertContains('deleted-role', $names);
        $this->assertNotContains('active-role', $names);
    }

    public function test_unauthenticated_is_redirected_from_trashed(): void
    {
        $this->get('/roles/trashed')->assertRedirect('/auth/login');
    }

    public function test_user_without_permission_cannot_access_trashed(): void
    {
        $guest = User::factory()->create();

        $this->actingAs($guest)->get('/roles/trashed')
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }

    // == RESTORE ==

    public function test_restore_redirects_to_trashed_with_success(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $ulid = $role->ulid;
        $role->delete();

        $response = $this->actingAs($this->user)
            ->patch("/roles/{$ulid}/restore");

        $response->assertRedirect(route('roles.trashed'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['ulid' => $ulid, 'deleted_at' => null]);
    }

    public function test_restored_role_no_longer_in_trashed_view(): void
    {
        $role = Role::create(['name' => 'comeback-role', 'guard_name' => 'web']);
        $ulid = $role->ulid;
        $role->delete();

        $this->actingAs($this->user)->patch("/roles/{$ulid}/restore");

        $response = $this->actingAs($this->user)->get('/roles/trashed');
        $roles = $response->viewData('roles');
        $names = collect($roles->items())->pluck('name')->all();
        $this->assertNotContains('comeback-role', $names);
    }

    public function test_restored_role_appears_in_index(): void
    {
        $role = Role::create(['name' => 'comeback-role', 'guard_name' => 'web']);
        $ulid = $role->ulid;
        $role->delete();

        $this->actingAs($this->user)->patch("/roles/{$ulid}/restore");

        $response = $this->actingAs($this->user)->get('/roles');
        $response->assertOk()->assertSee('comeback-role');
    }

    public function test_restore_returns_404_for_non_existent_ulid(): void
    {
        $this->actingAs($this->user)
            ->patch('/roles/non-existent-ulid/restore')
            ->assertNotFound();
    }

    public function test_unauthenticated_cannot_restore_role(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->delete();

        $this->patch("/roles/{$role->ulid}/restore")->assertRedirect('/auth/login');
    }

    public function test_user_without_permission_cannot_restore_role(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->delete();
        $guest = User::factory()->create();

        $this->actingAs($guest)
            ->patch("/roles/{$role->ulid}/restore")
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }
}
