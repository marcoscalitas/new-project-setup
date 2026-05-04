<?php

namespace Modules\Authorization\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

class TrashedRoleApiTest extends TestCase
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

    // == TRASHED ==

    public function test_can_list_trashed_roles(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'api']);
        $role->delete();

        $response = $this->getJson('/api/v1/roles/trashed', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('editor', $names);
    }

    public function test_trashed_list_does_not_include_active_roles(): void
    {
        Role::create(['name' => 'active-role', 'guard_name' => 'api']);
        $deleted = Role::create(['name' => 'deleted-role', 'guard_name' => 'api']);
        $deleted->delete();

        $response = $this->getJson('/api/v1/roles/trashed', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('deleted-role', $names);
        $this->assertNotContains('active-role', $names);
    }

    public function test_trashed_list_is_empty_when_no_deleted_roles(): void
    {
        Role::create(['name' => 'active', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/roles/trashed', $this->authHeaders());

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_cannot_access_trashed_roles(): void
    {
        $this->getJson('/api/v1/roles/trashed')->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_access_trashed_roles(): void
    {
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->getJson('/api/v1/roles/trashed', ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    public function test_trashed_roles_supports_pagination(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $role = Role::create(['name' => "role-{$i}", 'guard_name' => 'api']);
            $role->delete();
        }

        $response = $this->getJson('/api/v1/roles/trashed?per_page=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);
    }

    // == RESTORE ==

    public function test_can_restore_deleted_role(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'api']);
        $ulid = $role->ulid;
        $role->delete();

        $response = $this->patchJson("/api/v1/roles/{$ulid}/restore", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('id', $ulid)
            ->assertJsonPath('name', 'editor');

        $this->assertDatabaseHas('roles', ['ulid' => $ulid, 'deleted_at' => null]);
    }

    public function test_restored_role_appears_in_active_list(): void
    {
        $role = Role::create(['name' => 'comeback', 'guard_name' => 'api']);
        $ulid = $role->ulid;
        $role->delete();

        $this->patchJson("/api/v1/roles/{$ulid}/restore", [], $this->authHeaders());

        $response = $this->getJson('/api/v1/roles', $this->authHeaders());
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('comeback', $names);
    }

    public function test_cannot_restore_non_existent_role(): void
    {
        $this->patchJson('/api/v1/roles/non-existent-ulid/restore', [], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_cannot_restore_active_role(): void
    {
        $role = Role::create(['name' => 'active', 'guard_name' => 'api']);

        $this->patchJson("/api/v1/roles/{$role->ulid}/restore", [], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_unauthenticated_cannot_restore_role(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'api']);
        $role->delete();

        $this->patchJson("/api/v1/roles/{$role->ulid}/restore")
            ->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_restore_role(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'api']);
        $role->delete();
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->patchJson("/api/v1/roles/{$role->ulid}/restore", [], ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    // == SEARCH ==

    public function test_index_search_filters_roles_by_name(): void
    {
        Role::create(['name' => 'super-admin', 'guard_name' => 'api']);
        Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/roles?search=super', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('super-admin', $names);
        $this->assertNotContains('editor', $names);
    }

    public function test_index_search_returns_empty_for_no_match(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/roles?search=nonexistent', $this->authHeaders());

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    // == SORT ==

    public function test_index_sorts_roles_by_name_asc(): void
    {
        Role::create(['name' => 'zebra-role', 'guard_name' => 'api']);
        Role::create(['name' => 'alpha-role', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/roles?sort=name&direction=asc', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->values()->all();
        $this->assertEquals($names, collect($names)->sort()->values()->all());
    }

    public function test_index_sorts_roles_by_name_desc(): void
    {
        Role::create(['name' => 'alpha-role', 'guard_name' => 'api']);
        Role::create(['name' => 'zebra-role', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/roles?sort=name&direction=desc', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->values()->all();
        $this->assertEquals($names, collect($names)->sortDesc()->values()->all());
    }

    public function test_index_invalid_sort_column_falls_back_safely(): void
    {
        $response = $this->getJson('/api/v1/roles?sort=secret_column&direction=asc', $this->authHeaders());

        $response->assertOk();
    }

    // == PAGINATION ==

    public function test_index_returns_paginated_roles(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Role::create(['name' => "role-{$i}", 'guard_name' => 'api']);
        }

        $response = $this->getJson('/api/v1/roles?per_page=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_index_per_page_capped_at_100(): void
    {
        $response = $this->getJson('/api/v1/roles?per_page=500', $this->authHeaders());

        $response->assertOk();
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }
}
