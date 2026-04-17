<?php

namespace Modules\User\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Permission\Models\Permission;
use Modules\Permission\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
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

    // == LIST ==

    public function test_authenticated_user_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(4); // 3 + setUp user
    }

    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_user(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('name', 'John Doe')
            ->assertJsonPath('email', 'john@example.com');

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_create_user_with_roles(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'roles'                 => ['admin'],
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonCount(1, 'roles');
    }

    public function test_create_user_requires_name(): void
    {
        $response = $this->postJson('/api/users', [
            'email'                 => 'john@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_user_requires_email(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_user_requires_password(): void
    {
        $response = $this->postJson('/api/users', [
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_create_user_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/users', [
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => 'SecurePass1!',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_create_user_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'email'                 => 'taken@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_user_rejects_invalid_email(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'email'                 => 'not-valid',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_user_rejects_short_password(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'abc',
            'password_confirmation' => 'abc',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_create_user_hashes_password(): void
    {
        $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ], $this->authHeaders());

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotEquals('SecurePass1!', $user->password);
    }

    public function test_create_user_does_not_expose_password(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonMissing(['password']);
    }

    // == SHOW ==

    public function test_user_can_view_user(): void
    {
        $target = User::factory()->create(['name' => 'Maria Silva']);

        $response = $this->getJson("/api/users/{$target->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'Maria Silva');
    }

    public function test_view_user_returns_404_for_invalid_id(): void
    {
        $response = $this->getJson('/api/users/999', $this->authHeaders());

        $response->assertNotFound();
    }

    // == UPDATE ==

    public function test_user_can_update_user(): void
    {
        $target = User::factory()->create();

        $response = $this->putJson("/api/users/{$target->id}", [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('email', 'updated@example.com');
    }

    public function test_update_user_syncs_roles(): void
    {
        $target = User::factory()->create();
        Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $response = $this->putJson("/api/users/{$target->id}", [
            'name'  => $target->name,
            'roles' => ['editor'],
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'roles');
    }

    public function test_update_user_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $target = User::factory()->create();

        $response = $this->putJson("/api/users/{$target->id}", [
            'email' => 'taken@example.com',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // == DELETE ==

    public function test_user_can_delete_user(): void
    {
        $target = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$target->id}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_delete_user_returns_404_for_invalid_id(): void
    {
        $response = $this->deleteJson('/api/users/999', [], $this->authHeaders());

        $response->assertNotFound();
    }
}
