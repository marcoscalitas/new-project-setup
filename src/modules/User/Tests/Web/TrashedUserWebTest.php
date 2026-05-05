<?php

namespace Modules\User\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authorization\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class TrashedUserWebTest extends TestCase
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

    // == TRASHED VIEW ==

    public function test_trashed_returns_blade_view(): void
    {
        $response = $this->actingAs($this->user)->get('/users/trashed');

        $response->assertOk()
            ->assertViewIs('user::users.trashed')
            ->assertViewHas('users');
    }

    public function test_trashed_respects_page_length_for_browser(): void
    {
        User::factory()->count(12)->create()->each->delete();

        $response = $this->actingAs($this->user)->get('/users/trashed?per_page=5');

        $response->assertOk()
            ->assertSee(__('ui.rows_per_page'));

        $this->assertSame(5, $response->viewData('users')->perPage());
    }

    public function test_trashed_view_lists_only_deleted_users(): void
    {
        $active = User::factory()->create(['name' => 'Active User']);
        $deleted = User::factory()->create(['name' => 'Deleted User']);
        $deleted->delete();

        $response = $this->actingAs($this->user)->get('/users/trashed');

        $users = $response->viewData('users');
        $names = collect($users->items())->pluck('name')->all();
        $this->assertContains('Deleted User', $names);
        $this->assertNotContains('Active User', $names);
    }

    public function test_unauthenticated_is_redirected_from_trashed(): void
    {
        $this->get('/users/trashed')->assertRedirect('/auth/login');
    }

    public function test_user_without_permission_cannot_access_trashed(): void
    {
        $guest = User::factory()->create();

        $this->actingAs($guest)->get('/users/trashed')
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }

    // == RESTORE ==

    public function test_restore_redirects_to_trashed_with_success(): void
    {
        $target = User::factory()->create();
        $ulid = $target->ulid;
        $target->delete();

        $response = $this->actingAs($this->user)
            ->patch("/users/{$ulid}/restore");

        $response->assertRedirect(route('users.trashed'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['ulid' => $ulid, 'deleted_at' => null]);
    }

    public function test_restored_user_no_longer_in_trashed_view(): void
    {
        $target = User::factory()->create(['name' => 'Comeback User']);
        $ulid = $target->ulid;
        $target->delete();

        $this->actingAs($this->user)->patch("/users/{$ulid}/restore");

        $response = $this->actingAs($this->user)->get('/users/trashed');
        $users = $response->viewData('users');
        $names = collect($users->items())->pluck('name')->all();
        $this->assertNotContains('Comeback User', $names);
    }

    public function test_restored_user_appears_in_index(): void
    {
        $target = User::factory()->create(['name' => 'Comeback User']);
        $ulid = $target->ulid;
        $target->delete();

        $this->actingAs($this->user)->patch("/users/{$ulid}/restore");

        $response = $this->actingAs($this->user)->get('/users');
        $response->assertOk()->assertSee('Comeback User');
    }

    public function test_restore_returns_404_for_non_existent_ulid(): void
    {
        $this->actingAs($this->user)
            ->patch('/users/non-existent-ulid/restore')
            ->assertNotFound();
    }

    public function test_unauthenticated_cannot_restore_user(): void
    {
        $target = User::factory()->create();
        $target->delete();

        $this->patch("/users/{$target->ulid}/restore")->assertRedirect('/auth/login');
    }

    public function test_user_without_permission_cannot_restore(): void
    {
        $target = User::factory()->create();
        $target->delete();
        $guest = User::factory()->create();

        $this->actingAs($guest)
            ->patch("/users/{$target->ulid}/restore")
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }
}
