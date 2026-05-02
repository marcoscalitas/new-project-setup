<?php

namespace Modules\Settings\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Permission\Models\Permission;
use Modules\Settings\Models\Setting;
use Modules\User\Models\User;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $adminToken;

    private User $regular;
    private string $regularToken;

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

        $this->admin = User::factory()->create();
        $this->adminToken = $this->admin->createToken('test')->accessToken;
        $this->grantPermissions($this->admin, ['setting.list', 'setting.view', 'setting.update', 'setting.delete']);

        $this->regular = User::factory()->create();
        $this->regularToken = $this->regular->createToken('test')->accessToken;
    }

    private function grantPermissions(User $user, array $names): void
    {
        $perms = array_map(fn($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']), $names);
        $user->givePermissionTo($perms);
    }

    private function adminHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->adminToken];
    }

    private function regularHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->regularToken];
    }

    // == LIST ==

    public function test_admin_can_list_settings(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'My App']);

        $response = $this->getJson('/api/v1/settings', $this->adminHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data'])
            ->assertJsonFragment(['key' => 'site_name']);
    }

    public function test_unauthenticated_user_cannot_list_settings(): void
    {
        $this->getJson('/api/v1/settings')->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_list_settings(): void
    {
        $this->getJson('/api/v1/settings', $this->regularHeaders())->assertForbidden();
    }

    // == SHOW ==

    public function test_admin_can_view_single_setting(): void
    {
        Setting::updateOrCreate(['key' => 'maintenance'], ['value' => false]);

        $response = $this->getJson('/api/v1/settings/maintenance', $this->adminHeaders());

        $response->assertOk()
            ->assertJsonPath('data.key', 'maintenance');
    }

    public function test_show_returns_404_for_nonexistent_key(): void
    {
        $this->getJson('/api/v1/settings/nonexistent', $this->adminHeaders())->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_view_setting(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'App']);

        $this->getJson('/api/v1/settings/site_name')->assertUnauthorized();
    }

    // == UPDATE ==

    public function test_admin_can_update_setting(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'Old']);

        $response = $this->putJson('/api/v1/settings/site_name', ['value' => 'New'], $this->adminHeaders());

        $response->assertOk()
            ->assertJsonPath('data.key', 'site_name')
            ->assertJsonPath('data.value', 'New');

        $this->assertDatabaseHas('settings', ['key' => 'site_name']);
    }

    public function test_update_creates_setting_if_not_exists(): void
    {
        $response = $this->putJson('/api/v1/settings/new_key', ['value' => 'hello'], $this->adminHeaders());

        $response->assertOk();
        $this->assertDatabaseHas('settings', ['key' => 'new_key']);
    }

    public function test_update_requires_value(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'App']);

        $this->putJson('/api/v1/settings/site_name', [], $this->adminHeaders())
            ->assertUnprocessable();
    }

    public function test_user_without_permission_cannot_update_setting(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'App']);

        $this->putJson('/api/v1/settings/site_name', ['value' => 'New'], $this->regularHeaders())
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_update_setting(): void
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'App']);
        $this->putJson('/api/v1/settings/site_name', ['value' => 'New'])->assertUnauthorized();
    }

    // == DESTROY ==

    public function test_admin_can_delete_setting(): void
    {
        Setting::updateOrCreate(['key' => 'tmp'], ['value' => 'bye']);

        $this->deleteJson('/api/v1/settings/tmp', [], $this->adminHeaders())
            ->assertNoContent();

        $this->assertDatabaseMissing('settings', ['key' => 'tmp']);
    }

    public function test_destroy_returns_404_for_nonexistent_key(): void
    {
        $this->deleteJson('/api/v1/settings/ghost', [], $this->adminHeaders())
            ->assertNotFound();
    }

    public function test_user_without_permission_cannot_delete_setting(): void
    {
        Setting::updateOrCreate(['key' => 'tmp'], ['value' => 'bye']);

        $this->deleteJson('/api/v1/settings/tmp', [], $this->regularHeaders())
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_delete_setting(): void
    {
        Setting::updateOrCreate(['key' => 'tmp'], ['value' => 'bye']);
        $this->deleteJson('/api/v1/settings/tmp', [])->assertUnauthorized();
    }
}
