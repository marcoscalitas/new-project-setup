<?php

namespace Modules\User\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Authorization\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class TrashedUserApiTest extends TestCase
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
        foreach (['user.list', 'user.view', 'user.create', 'user.update', 'user.delete'] as $name) {
            $perms[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }
        $this->user->givePermissionTo($perms);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    // == TRASHED ==

    public function test_can_list_trashed_users(): void
    {
        $target = User::factory()->create();
        $target->delete();

        $response = $this->getJson('/api/v1/users/trashed', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($target->ulid, $ids);
    }

    public function test_trashed_list_does_not_include_active_users(): void
    {
        User::factory()->create(['name' => 'Active User']);
        $deleted = User::factory()->create(['name' => 'Deleted User']);
        $deleted->delete();

        $response = $this->getJson('/api/v1/users/trashed', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('Deleted User', $names);
        $this->assertNotContains('Active User', $names);
    }

    public function test_trashed_list_is_empty_when_no_deleted_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users/trashed', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_cannot_access_trashed(): void
    {
        $this->getJson('/api/v1/users/trashed')
            ->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_access_trashed(): void
    {
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->getJson('/api/v1/users/trashed', ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    public function test_trashed_list_supports_pagination(): void
    {
        User::factory()->count(20)->create()->each->delete();

        $response = $this->getJson('/api/v1/users/trashed?per_page=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_trashed_per_page_is_capped_at_100(): void
    {
        User::factory()->count(5)->create()->each->delete();

        $response = $this->getJson('/api/v1/users/trashed?per_page=999', $this->authHeaders());

        $response->assertOk();
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    // == RESTORE ==

    public function test_can_restore_deleted_user(): void
    {
        $target = User::factory()->create();
        $ulid   = $target->ulid;
        $target->delete();

        $response = $this->patchJson("/api/v1/users/{$ulid}/restore", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('id', $ulid);

        $this->assertDatabaseHas('users', ['ulid' => $ulid, 'deleted_at' => null]);
    }

    public function test_restored_user_appears_in_active_list(): void
    {
        $target = User::factory()->create();
        $ulid   = $target->ulid;
        $target->delete();

        $this->patchJson("/api/v1/users/{$ulid}/restore", [], $this->authHeaders());

        $response = $this->getJson('/api/v1/users', $this->authHeaders());
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($ulid, $ids);
    }

    public function test_cannot_restore_non_existent_user(): void
    {
        $this->patchJson('/api/v1/users/non-existent-ulid/restore', [], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_cannot_restore_active_user(): void
    {
        $target = User::factory()->create();

        $this->patchJson("/api/v1/users/{$target->ulid}/restore", [], $this->authHeaders())
            ->assertNotFound();
    }

    public function test_unauthenticated_cannot_restore_user(): void
    {
        $target = User::factory()->create();
        $target->delete();

        $this->patchJson("/api/v1/users/{$target->ulid}/restore")
            ->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_restore(): void
    {
        $target = User::factory()->create();
        $target->delete();
        $guest = User::factory()->create();
        $token = $guest->createToken('test')->accessToken;

        $this->patchJson("/api/v1/users/{$target->ulid}/restore", [], ['Authorization' => 'Bearer ' . $token])
            ->assertForbidden();
    }

    // == SEARCH ==

    public function test_index_search_filters_by_name(): void
    {
        User::factory()->create(['name' => 'Alice Smith']);
        User::factory()->create(['name' => 'Bob Jones']);

        $response = $this->getJson('/api/v1/users?search=Alice', $this->authHeaders());

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('Alice Smith', $names);
        $this->assertNotContains('Bob Jones', $names);
    }

    public function test_index_search_filters_by_email(): void
    {
        User::factory()->create(['name' => 'Email Alpha', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Email Beta', 'email' => 'bob@example.com']);

        $response = $this->getJson('/api/v1/users?search=alice', $this->authHeaders());

        $response->assertOk();
        $emails = collect($response->json('data'))->pluck('email')->all();
        $this->assertContains('alice@example.com', $emails);
        $this->assertNotContains('bob@example.com', $emails);
    }

    public function test_index_search_returns_empty_when_no_match(): void
    {
        User::factory()->create(['name' => 'Alice Smith']);

        $response = $this->getJson('/api/v1/users?search=nonexistent', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    // == SORT ==

    public function test_index_sorts_by_name_asc(): void
    {
        User::factory()->create(['name' => 'Charlie']);
        User::factory()->create(['name' => 'Alice']);

        $response = $this->getJson('/api/v1/users?sort=name&direction=asc', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->values()->all();
        $this->assertEquals($names, collect($names)->sort()->values()->all());
    }

    public function test_index_sorts_by_name_desc(): void
    {
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Charlie']);

        $response = $this->getJson('/api/v1/users?sort=name&direction=desc', $this->authHeaders());

        $names = collect($response->json('data'))->pluck('name')->values()->all();
        $this->assertEquals($names, collect($names)->sortDesc()->values()->all());
    }

    public function test_index_invalid_sort_column_falls_back_to_name(): void
    {
        $response = $this->getJson('/api/v1/users?sort=password&direction=asc', $this->authHeaders());

        $response->assertOk();
    }

    public function test_index_invalid_direction_falls_back_to_asc(): void
    {
        $response = $this->getJson('/api/v1/users?sort=name&direction=DROP+TABLE', $this->authHeaders());

        $response->assertOk();
    }

    // == PAGINATION ==

    public function test_index_returns_paginated_response(): void
    {
        User::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/users?per_page=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5);
    }

    public function test_index_per_page_capped_at_100(): void
    {
        $response = $this->getJson('/api/v1/users?per_page=999', $this->authHeaders());

        $response->assertOk();
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    public function test_index_pagination_meta_is_correct(): void
    {
        User::factory()->count(4)->create();

        $response = $this->getJson('/api/v1/users?per_page=3', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('meta.total', 5) // 4 + setUp user
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2);
    }
}
