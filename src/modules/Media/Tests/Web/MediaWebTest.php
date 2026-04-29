<?php

namespace Modules\Media\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class MediaWebTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->grantPermissions($this->admin, ['media.list', 'media.delete']);
        $this->createSidebarPermissions();
    }

    private function grantPermissions(User $user, array $names): void
    {
        $perms = array_map(fn($n) => Permission::firstOrCreate(['name' => $n, 'guard_name' => 'web']), $names);
        $user->givePermissionTo($perms);
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

    // == INDEX ==

    public function test_admin_can_view_media_index(): void
    {
        $this->createMedia($this->admin);

        $this->actingAs($this->admin)->get('/media')
            ->assertOk()
            ->assertSee('photo.jpg');
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/media')->assertRedirect();
    }

    public function test_index_returns_json_for_api_request(): void
    {
        $this->createMedia($this->admin);

        $this->actingAs($this->admin)->getJson('/media')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_user_without_permission_cannot_list_media(): void
    {
        $noPerms = User::factory()->create();

        $this->actingAs($noPerms)->getJson('/media')
            ->assertForbidden();
    }

    // == SHOW ==

    public function test_admin_can_view_media_detail(): void
    {
        $media = $this->createMedia($this->admin);

        $this->actingAs($this->admin)->get("/media/{$media->id}")
            ->assertOk()
            ->assertSee('photo.jpg');
    }

    public function test_show_returns_404_for_nonexistent_media(): void
    {
        $this->actingAs($this->admin)->get('/media/99999')
            ->assertNotFound();
    }

    // == DESTROY ==

    public function test_admin_can_delete_media(): void
    {
        $media = $this->createMedia($this->admin);

        $this->actingAs($this->admin)->delete("/media/{$media->id}")
            ->assertRedirect('/media');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }
}
