<?php

namespace Modules\Permission\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class TrashedPermissionApiTest extends TestCase
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

    // == TRASHED ==

    public function test_can_list_trashed_permissions(): void
    {
        $perm = Permission::create(['name' => 'post.delete', 'guard_name' => 'api']);
        $perm->delete();

        $response = $this->getJson('/api/v1/permissions/trashed', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('post.delete', $names);
    }

    public function test_trashed_list_does_not_include_active_permissions(): void
    {
        Permission::create(['name' => 'active.perm', 'guard_name' => 'api']);
        $deleted = Permission::create(['name' => 'deleted.perm', 'guard_name' => 'api']);
        $deleted->delete();

        $response = $this->getJson('/api/v1/permissions/trashed', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('deleted.perm', $names);
        $this->assertNotContains('active.perm', $names);
    }

    public function test_trashed_list_is_empty_when_no_deleted_permissions(): void
    {
        Permission::create(['name' => 'active.perm', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/permissions/trashed', $this->authHeaders());

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_cannot_access_trashed_permissions(): void
    {
        $this->getJson('/api/v1/permissions/trashed')->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_access_trashed(): void
    {
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->getJson('/api/v1/permissions/trashed', ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    public function test_trashed_permissions_supports_pagination(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $p = Permission::create(['name' => "perm.{$i}", 'guard_name' => 'api']);
            $p->delete();
        }

        $response = $this->getJson('/api/v1/permissions/trashed?per_page=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);
    }

    // == RESTORE ==

    public function test_can_restore_deleted_permission(): void
    {
        $perm = Permission::create(['name' => 'post.delete', 'guard_name' => 'api']);
        $ulid = $perm->ulid;
        $perm->delete();

        $response = $this->patchJson("/api/v1/permissions/{$ulid}/restore", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('id', $ulid)
            ->assertJsonPath('name', 'post.delete');

        $this->assertDatabaseHas('permissions', ['ulid' => $ulid, 'deleted_at' => null]);
    }

    public function test_restored_permission_appears_in_active_list(): void
    {
        $perm = Permission::create(['name' => 'comeback.perm', 'guard_name' => 'api']);
        $ulid = $perm->ulid;
        $perm->delete();

        $this->patchJson("/api/v1/permissions/{$ulid}/restore", [], $this->authHeaders());

        $response = $this->getJson('/api/v1/permissions', $this->authHeaders());
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('comeback.perm', $names);
    }

    public function test_cannot_restore_non_existent_permission(): void
    {
        $this->patchJson('/api/v1/permissions/non-existent-ulid/restore', [], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_cannot_restore_active_permission(): void
    {
        $perm = Permission::create(['name' => 'active.perm', 'guard_name' => 'api']);

        $this->patchJson("/api/v1/permissions/{$perm->ulid}/restore", [], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_unauthenticated_cannot_restore_permission(): void
    {
        $perm = Permission::create(['name' => 'post.delete', 'guard_name' => 'api']);
        $perm->delete();

        $this->patchJson("/api/v1/permissions/{$perm->ulid}/restore")
            ->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_restore(): void
    {
        $perm = Permission::create(['name' => 'post.delete', 'guard_name' => 'api']);
        $perm->delete();
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->patchJson("/api/v1/permissions/{$perm->ulid}/restore", [], ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    // == SEARCH ==

    public function test_index_search_filters_permissions_by_name(): void
    {
        Permission::create(['name' => 'user.create', 'guard_name' => 'api']);
        Permission::create(['name' => 'post.delete', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/permissions?search=user', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertTrue(collect($names)->every(fn ($n) => str_contains($n, 'user')));
    }

    public function test_index_search_returns_empty_for_no_match(): void
    {
        Permission::create(['name' => 'post.delete', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/permissions?search=xyz_nonexistent', $this->authHeaders());

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    // == SORT ==

    public function test_index_sorts_permissions_by_name_asc(): void
    {
        Permission::create(['name' => 'zebra.perm', 'guard_name' => 'api']);
        Permission::create(['name' => 'alpha.perm', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/permissions?sort=name&direction=asc', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->values()->all();
        $this->assertEquals($names, collect($names)->sort()->values()->all());
    }

    public function test_index_invalid_sort_column_falls_back_safely(): void
    {
        $response = $this->getJson('/api/v1/permissions?sort=guard_injection&direction=asc', $this->authHeaders());

        $response->assertOk();
    }

    // == PAGINATION ==

    public function test_index_returns_paginated_permissions(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Permission::create(['name' => "extra.perm{$i}", 'guard_name' => 'api']);
        }

        $response = $this->getJson('/api/v1/permissions?per_page=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5);
    }

    public function test_index_per_page_capped_at_100(): void
    {
        $response = $this->getJson('/api/v1/permissions?per_page=500', $this->authHeaders());

        $response->assertOk();
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }
}
