<?php

namespace Modules\Permission\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Permission\Models\Permission;
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

        $response = $this->getJson('/api/permissions/permissions', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(7);
    }

    public function test_unauthenticated_user_cannot_list_permissions(): void
    {
        $response = $this->getJson('/api/permissions/permissions');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_permission(): void
    {
        $response = $this->postJson('/api/permissions/permissions', [
            'name' => 'user.list',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('name', 'user.list');

        $this->assertDatabaseHas('permissions', ['name' => 'user.list']);
    }

    public function test_create_permission_requires_name(): void
    {
        $response = $this->postJson('/api/permissions/permissions', [], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_permission_rejects_duplicate_name(): void
    {
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->postJson('/api/permissions/permissions', [
            'name' => 'user.list',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // == SHOW ==

    public function test_user_can_view_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->getJson("/api/permissions/permissions/{$permission->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'user.list');
    }

    public function test_view_permission_returns_404_for_invalid_id(): void
    {
        $response = $this->getJson('/api/permissions/permissions/999', $this->authHeaders());

        $response->assertNotFound();
    }

    // == UPDATE ==

    public function test_user_can_update_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->putJson("/api/permissions/permissions/{$permission->id}", [
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

        $response = $this->putJson("/api/permissions/permissions/{$permission->id}", [
            'name' => 'user.list',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // == DELETE ==

    public function test_user_can_delete_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->deleteJson("/api/permissions/permissions/{$permission->id}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    public function test_delete_permission_returns_404_for_invalid_id(): void
    {
        $response = $this->deleteJson('/api/permissions/permissions/999', [], $this->authHeaders());

        $response->assertNotFound();
    }
}
