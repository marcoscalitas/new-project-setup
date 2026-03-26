<?php

namespace Modules\Permission\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class PermissionWebTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    // == LIST ==

    public function test_authenticated_user_can_list_permissions(): void
    {
        Permission::create(['name' => 'user.list', 'guard_name' => 'api']);
        Permission::create(['name' => 'user.view', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->getJson('/permissions/permissions');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_unauthenticated_user_cannot_list_permissions(): void
    {
        $response = $this->getJson('/permissions/permissions');

        $response->assertUnauthorized();
    }

    // == STORE ==

    public function test_user_can_create_permission(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/permissions/permissions', ['name' => 'user.list']);

        $response->assertCreated()
            ->assertJsonPath('name', 'user.list');

        $this->assertDatabaseHas('permissions', ['name' => 'user.list']);
    }

    public function test_create_permission_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/permissions/permissions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // == SHOW ==

    public function test_user_can_view_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->getJson("/permissions/permissions/{$permission->id}");

        $response->assertOk()
            ->assertJsonPath('name', 'user.list');
    }

    // == UPDATE ==

    public function test_user_can_update_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->putJson("/permissions/permissions/{$permission->id}", ['name' => 'user.view']);

        $response->assertOk()
            ->assertJsonPath('name', 'user.view');

        $this->assertDatabaseHas('permissions', ['name' => 'user.view']);
    }

    // == DESTROY ==

    public function test_user_can_delete_permission(): void
    {
        $permission = Permission::create(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->actingAs($this->user)
            ->deleteJson("/permissions/permissions/{$permission->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }
}
