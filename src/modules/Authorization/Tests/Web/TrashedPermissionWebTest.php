<?php

namespace Modules\Authorization\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authorization\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class TrashedPermissionWebTest extends TestCase
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

    // == TRASHED VIEW ==

    public function test_trashed_returns_blade_view(): void
    {
        $response = $this->actingAs($this->user)->get('/permissions/trashed');

        $response->assertOk()
            ->assertViewIs('authorization::permissions.trashed')
            ->assertViewHas('permissions');
    }

    public function test_trashed_view_lists_only_deleted_permissions(): void
    {
        Permission::create(['name' => 'active.perm', 'guard_name' => 'web']);
        $deleted = Permission::create(['name' => 'deleted.perm', 'guard_name' => 'web']);
        $deleted->delete();

        $response = $this->actingAs($this->user)->get('/permissions/trashed');

        $permissions = $response->viewData('permissions');
        $names = collect($permissions->items())->pluck('name')->all();
        $this->assertContains('deleted.perm', $names);
        $this->assertNotContains('active.perm', $names);
    }

    public function test_trashed_search_filters_deleted_permissions(): void
    {
        Permission::create(['name' => 'archived.alpha', 'guard_name' => 'web'])->delete();
        Permission::create(['name' => 'archived.beta', 'guard_name' => 'web'])->delete();

        $response = $this->actingAs($this->user)->get('/permissions/trashed?search=alpha');

        $response->assertOk()
            ->assertSee('archived.alpha')
            ->assertDontSee('archived.beta');
    }

    public function test_unauthenticated_is_redirected_from_trashed(): void
    {
        $this->get('/permissions/trashed')->assertRedirect('/auth/login');
    }

    public function test_user_without_permission_cannot_access_trashed(): void
    {
        $guest = User::factory()->create();

        $this->actingAs($guest)->get('/permissions/trashed')
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }

    // == RESTORE ==

    public function test_restore_redirects_to_trashed_with_success(): void
    {
        $perm = Permission::create(['name' => 'post.delete', 'guard_name' => 'web']);
        $ulid = $perm->ulid;
        $perm->delete();

        $response = $this->actingAs($this->user)
            ->patch("/permissions/{$ulid}/restore");

        $response->assertRedirect(route('permissions.trashed'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('permissions', ['ulid' => $ulid, 'deleted_at' => null]);
    }

    public function test_restored_permission_no_longer_in_trashed_view(): void
    {
        $perm = Permission::create(['name' => 'comeback.perm', 'guard_name' => 'web']);
        $ulid = $perm->ulid;
        $perm->delete();

        $this->actingAs($this->user)->patch("/permissions/{$ulid}/restore");

        $response = $this->actingAs($this->user)->get('/permissions/trashed');
        $permissions = $response->viewData('permissions');
        $names = collect($permissions->items())->pluck('name')->all();
        $this->assertNotContains('comeback.perm', $names);
    }

    public function test_restored_permission_appears_in_index(): void
    {
        $perm = Permission::create(['name' => 'comeback.perm', 'guard_name' => 'web']);
        $ulid = $perm->ulid;
        $perm->delete();

        $this->actingAs($this->user)->patch("/permissions/{$ulid}/restore");

        $response = $this->actingAs($this->user)->get('/permissions');
        $response->assertOk()->assertSee('comeback.perm');
    }

    public function test_restore_returns_404_for_non_existent_ulid(): void
    {
        $this->actingAs($this->user)
            ->patch('/permissions/non-existent-ulid/restore')
            ->assertNotFound();
    }

    public function test_unauthenticated_cannot_restore_permission(): void
    {
        $perm = Permission::create(['name' => 'post.delete', 'guard_name' => 'web']);
        $perm->delete();

        $this->patch("/permissions/{$perm->ulid}/restore")->assertRedirect('/auth/login');
    }

    public function test_user_without_permission_cannot_restore(): void
    {
        $perm = Permission::create(['name' => 'post.delete', 'guard_name' => 'web']);
        $perm->delete();
        $guest = User::factory()->create();

        $this->actingAs($guest)
            ->patch("/permissions/{$perm->ulid}/restore")
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }
}
