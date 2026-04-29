<?php

namespace Modules\Settings\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Permission\Models\Permission;
use Modules\Settings\Models\Setting;
use Modules\User\Models\User;
use Tests\TestCase;

class SettingsWebTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->grantPermissions($this->admin, ['setting.list', 'setting.view', 'setting.update', 'setting.delete']);
        $this->createSidebarPermissions();
    }

    private function grantPermissions(User $user, array $names): void
    {
        $perms = array_map(fn($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']), $names);
        $user->givePermissionTo($perms);
    }

    // == INDEX ==

    public function test_admin_can_view_settings_page(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'My App']);

        $this->actingAs($this->admin)->get('/settings')
            ->assertOk()
            ->assertSee('site_name');
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/settings')->assertRedirect();
    }

    public function test_index_returns_json_for_api_request(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'My App']);

        $this->actingAs($this->admin)->getJson('/settings')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    // == SHOW ==

    public function test_admin_can_view_single_setting_page(): void
    {
        Setting::updateOrCreate(['key' => 'maintenance'], ['value' => false]);

        $this->actingAs($this->admin)->get('/settings/maintenance')
            ->assertOk()
            ->assertSee('maintenance');
    }

    public function test_show_returns_404_for_nonexistent_key(): void
    {
        $this->actingAs($this->admin)->get('/settings/ghost')
            ->assertNotFound();
    }

    // == UPDATE ==

    public function test_admin_can_update_setting(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'Old']);

        $this->actingAs($this->admin)
            ->put('/settings/site_name', ['value' => 'New'])
            ->assertRedirect('/settings');

        $this->assertDatabaseHas('settings', ['key' => 'site_name']);
    }

    // == DESTROY ==

    public function test_admin_can_delete_setting(): void
    {
        Setting::updateOrCreate(['key' => 'tmp'], ['value' => 'bye']);

        $this->actingAs($this->admin)
            ->delete('/settings/tmp')
            ->assertRedirect('/settings');

        $this->assertDatabaseMissing('settings', ['key' => 'tmp']);
    }

    public function test_user_without_permission_cannot_list_settings(): void
    {
        $noPerms = User::factory()->create();

        $this->actingAs($noPerms)->getJson('/settings')
            ->assertForbidden();
    }
}
