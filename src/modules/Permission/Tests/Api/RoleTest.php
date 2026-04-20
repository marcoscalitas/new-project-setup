<?php

namespace Modules\Permission\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Permission\Models\Permission;
use Modules\Permission\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(storage_path('oauth-private.key'))) {
            $this->artisan('passport:keys', ['--force' => true]);
        }

        Client::create([
            'name'          => 'Test Personal Client',
            'secret'        => null,
            'redirect_uris' => [],
            'grant_types'   => ['personal_access'],
            'provider'      => 'users',
            'revoked'       => false,
        ]);

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->accessToken;

        $this->grantPermissions();
    }

    private function grantPermissions(): void
    {
        $perms = [];
        foreach (['role.list', 'role.view', 'role.create', 'role.update', 'role.delete'] as $name) {
            $perms[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }
        $this->user->givePermissionTo($perms);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    // == LIST ==

    public function test_authenticated_user_can_list_roles(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $response = $this->getJson('/api/roles', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_unauthenticated_user_cannot_list_roles(): void
    {
        $response = $this->getJson('/api/roles');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_role(): void
    {
        $response = $this->postJson('/api/roles', [
            'name' => 'admin',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('name', 'admin');

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }

    public function test_create_role_with_permissions(): void
    {
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'user.create', 'guard_name' => 'api']);

        $response = $this->postJson('/api/roles', [
            'name'        => 'admin',
            'permissions' => ['user.list', 'user.create'],
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonCount(2, 'permissions');
    }

    public function test_create_role_requires_name(): void
    {
        $response = $this->postJson('/api/roles', [], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_role_rejects_duplicate_name(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->postJson('/api/roles', [
            'name' => 'admin',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_role_allows_same_name_in_different_guard(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $response = $this->postJson('/api/roles', [
            'name' => 'admin',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('name', 'admin');
    }

    public function test_create_role_validates_permissions_exist(): void
    {
        $response = $this->postJson('/api/roles', [
            'name'        => 'admin',
            'permissions' => ['nonexistent.permission'],
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['permissions.0']);
    }

    // == SHOW ==

    public function test_user_can_view_role(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->getJson("/api/roles/{$role->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'admin');
    }

    public function test_view_role_returns_404_for_invalid_id(): void
    {
        $response = $this->getJson('/api/roles/999', $this->authHeaders());

        $response->assertNotFound();
    }

    // == UPDATE ==

    public function test_user_can_update_role(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->putJson("/api/roles/{$role->id}", [
            'name' => 'super-admin',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'super-admin');

        $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
    }

    public function test_update_role_syncs_permissions(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'user.create', 'guard_name' => 'api']);

        $response = $this->putJson("/api/roles/{$role->id}", [
            'name'        => 'admin',
            'permissions' => ['user.list'],
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'permissions');
    }

    public function test_update_role_rejects_duplicate_name(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $response = $this->putJson("/api/roles/{$role->id}", [
            'name' => 'admin',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_role_allows_keeping_own_name(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->putJson("/api/roles/{$role->id}", [
            'name' => 'admin',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'admin');
    }

    public function test_update_role_with_empty_permissions_removes_all(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $perm = Permission::create(['name' => 'test.perm', 'guard_name' => 'api']);
        $role->givePermissionTo($perm);

        $response = $this->putJson("/api/roles/{$role->id}", [
            'name'        => 'admin',
            'permissions' => [],
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(0, 'permissions');
    }

    // == DELETE ==

    public function test_user_can_delete_role(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $response = $this->deleteJson("/api/roles/{$role->id}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertSoftDeleted('roles', ['id' => $role->id]);
    }

    public function test_cannot_delete_admin_role(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->deleteJson("/api/roles/{$role->id}", [], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_delete_role_returns_404_for_invalid_id(): void
    {
        $response = $this->deleteJson('/api/roles/999', [], $this->authHeaders());

        $response->assertNotFound();
    }

    public function test_deleted_role_is_not_listed(): void
    {
        $role = Role::create(['name' => 'temp-role', 'guard_name' => 'api']);
        $this->deleteJson("/api/roles/{$role->id}", [], $this->authHeaders());

        $response = $this->getJson('/api/roles', $this->authHeaders());

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertNotContains('temp-role', $names);
    }

    public function test_can_create_role_with_same_name_after_soft_delete(): void
    {
        $role = Role::create(['name' => 'reusable', 'guard_name' => 'api']);
        $this->deleteJson("/api/roles/{$role->id}", [], $this->authHeaders());

        $response = $this->postJson('/api/roles', [
            'name' => 'reusable',
        ], $this->authHeaders());

        $response->assertCreated();
    }
}
