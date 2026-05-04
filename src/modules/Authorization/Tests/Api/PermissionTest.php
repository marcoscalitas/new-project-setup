<?php

namespace Modules\Authorization\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Authorization\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class PermissionTest extends TestCase
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
        foreach (['permission.list', 'permission.view', 'permission.create', 'permission.update', 'permission.delete'] as $name) {
            $perms[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }
        $this->user->givePermissionTo($perms);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    // == LIST ==

    public function test_authenticated_user_can_list_permissions(): void
    {
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'user.create', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/permissions', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(7, 'data');
    }

    public function test_unauthenticated_user_cannot_list_permissions(): void
    {
        $response = $this->getJson('/api/v1/permissions');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_permission(): void
    {
        $response = $this->postJson('/api/v1/permissions', [
            'name' => 'user.list',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('name', 'user.list');

        $this->assertDatabaseHas('permissions', ['name' => 'user.list']);
    }

    public function test_create_permission_requires_name(): void
    {
        $response = $this->postJson('/api/v1/permissions', [], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_permission_rejects_duplicate_name(): void
    {
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->postJson('/api/v1/permissions', [
            'name' => 'user.list',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_permission_allows_same_name_in_different_guard(): void
    {
        Permission::create(['name' => 'post.create', 'guard_name' => 'web']);

        $response = $this->postJson('/api/v1/permissions', [
            'name' => 'post.create',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('name', 'post.create');
    }

    // == SHOW ==

    public function test_user_can_view_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->getJson("/api/v1/permissions/{$permission->ulid}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'user.list');
    }

    public function test_view_permission_returns_404_for_invalid_id(): void
    {
        $response = $this->getJson('/api/v1/permissions/999', $this->authHeaders());

        $response->assertNotFound();
    }

    // == UPDATE ==

    public function test_user_can_update_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->putJson("/api/v1/permissions/{$permission->ulid}", [
            'name' => 'user.view',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'user.view');

        $this->assertDatabaseHas('permissions', ['name' => 'user.view']);
    }

    public function test_update_permission_rejects_duplicate_name(): void
    {
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);
        $permission = Permission::create(['name' => 'user.create', 'guard_name' => 'api']);

        $response = $this->putJson("/api/v1/permissions/{$permission->ulid}", [
            'name' => 'user.list',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_permission_allows_keeping_own_name(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->putJson("/api/v1/permissions/{$permission->ulid}", [
            'name' => 'user.list',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'user.list');
    }

    // == DELETE ==

    public function test_user_can_delete_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->deleteJson("/api/v1/permissions/{$permission->ulid}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertSoftDeleted('permissions', ['id' => $permission->id]);
    }

    public function test_delete_permission_returns_404_for_invalid_id(): void
    {
        $response = $this->deleteJson('/api/v1/permissions/999', [], $this->authHeaders());

        $response->assertNotFound();
    }

    public function test_deleted_permission_is_not_listed(): void
    {
        $perm = Permission::create(['name' => 'temp.perm', 'guard_name' => 'api']);
        $this->deleteJson("/api/v1/permissions/{$perm->ulid}", [], $this->authHeaders());

        $response = $this->getJson('/api/v1/permissions', $this->authHeaders());

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertNotContains('temp.perm', $names);
    }

    public function test_can_create_permission_with_same_name_after_soft_delete(): void
    {
        $perm = Permission::create(['name' => 'reusable.perm', 'guard_name' => 'api']);
        $this->deleteJson("/api/v1/permissions/{$perm->ulid}", [], $this->authHeaders());

        $response = $this->postJson('/api/v1/permissions', [
            'name' => 'reusable.perm',
        ], $this->authHeaders());

        $response->assertCreated();
    }

    public function test_user_without_permission_cannot_list_permissions(): void
    {
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->getJson('/api/v1/permissions', ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_create_permission(): void
    {
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->postJson('/api/v1/permissions', ['name' => 'unauthorized.perm'], ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_delete_permission(): void
    {
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;
        $perm  = Permission::create(['name' => 'target.perm', 'guard_name' => 'api']);

        $this->deleteJson("/api/v1/permissions/{$perm->ulid}", [], ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }
}
