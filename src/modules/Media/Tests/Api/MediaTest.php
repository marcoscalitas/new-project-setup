<?php

namespace Modules\Media\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class MediaTest extends TestCase
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
        $this->grantPermissions($this->admin, ['media.list', 'media.delete']);

        $this->regular = User::factory()->create();
        $this->regularToken = $this->regular->createToken('test')->accessToken;
    }

    private function grantPermissions(User $user, array $names): void
    {
        $perms = array_map(fn($n) => Permission::firstOrCreate(['name' => $n, 'guard_name' => 'api']), $names);
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

    private function createMedia(User $model, array $overrides = []): Media
    {
        return Media::create([
            'model_type'             => User::class,
            'model_id'               => $model->id,
            'uuid'                   => (string) Str::uuid(),
            'collection_name'        => $overrides['collection'] ?? 'avatars',
            'name'                   => $overrides['name'] ?? 'photo',
            'file_name'              => $overrides['file_name'] ?? 'photo.jpg',
            'mime_type'              => 'image/jpeg',
            'disk'                   => 'public',
            'conversions_disk'       => 'public',
            'size'                   => 1024,
            'manipulations'          => [],
            'custom_properties'      => [],
            'generated_conversions'  => [],
            'responsive_images'      => [],
            'order_column'           => 1,
        ]);
    }

    // == LIST ==

    public function test_admin_can_list_media(): void
    {
        $this->createMedia($this->admin);
        $this->createMedia($this->admin);

        $response = $this->getJson('/api/v1/media', $this->adminHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_list_media(): void
    {
        $this->getJson('/api/v1/media')->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_list_media(): void
    {
        $this->getJson('/api/v1/media', $this->regularHeaders())->assertForbidden();
    }

    // == SHOW ==

    public function test_admin_can_view_media_item(): void
    {
        $media = $this->createMedia($this->admin);

        $response = $this->getJson("/api/v1/media/{$media->id}", $this->adminHeaders());

        $response->assertOk()
            ->assertJsonPath('data.id', $media->id)
            ->assertJsonPath('data.file_name', 'photo.jpg');
    }

    public function test_show_returns_404_for_nonexistent_media(): void
    {
        $this->getJson('/api/v1/media/99999', $this->adminHeaders())->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_view_media(): void
    {
        $media = $this->createMedia($this->admin);

        $this->getJson("/api/v1/media/{$media->id}")->assertUnauthorized();
    }

    // == DESTROY ==

    public function test_admin_can_delete_media(): void
    {
        $media = $this->createMedia($this->admin);

        $this->deleteJson("/api/v1/media/{$media->id}", [], $this->adminHeaders())
            ->assertNoContent();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_media(): void
    {
        $this->deleteJson('/api/v1/media/99999', [], $this->adminHeaders())
            ->assertNotFound();
    }

    public function test_user_without_permission_cannot_delete_media(): void
    {
        $media = $this->createMedia($this->admin);

        $this->deleteJson("/api/v1/media/{$media->id}", [], $this->regularHeaders())
            ->assertForbidden();
    }
}
