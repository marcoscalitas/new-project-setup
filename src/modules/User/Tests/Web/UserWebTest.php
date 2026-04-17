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
            ->assertJsonCount(4);
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
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }
}
