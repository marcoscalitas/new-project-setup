<?php

namespace Modules\User\Tests\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Client;
use Modules\Authorization\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('minio');

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

        $permission = Permission::firstOrCreate(['name' => 'user.update', 'guard_name' => 'api']);
        $this->user->givePermissionTo($permission);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_user_can_upload_avatar(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->postJson(
            "/api/v1/users/{$this->user->ulid}/avatar",
            ['avatar' => $file],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'avatar_url']])
            ->assertJsonPath('data.id', $this->user->ulid);

        $this->assertNotNull($response->json('data.avatar_url'));
        $this->assertCount(1, $this->user->fresh()->getMedia('avatar'));
    }

    public function test_uploading_avatar_replaces_previous_one(): void
    {
        $first = UploadedFile::fake()->image('first.jpg');
        $second = UploadedFile::fake()->image('second.jpg');

        $this->postJson("/api/v1/users/{$this->user->ulid}/avatar", ['avatar' => $first], $this->authHeaders());
        $this->postJson("/api/v1/users/{$this->user->ulid}/avatar", ['avatar' => $second], $this->authHeaders());

        $this->assertCount(1, $this->user->fresh()->getMedia('avatar'));
    }

    public function test_avatar_upload_validates_mime_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson(
            "/api/v1/users/{$this->user->ulid}/avatar",
            ['avatar' => $file],
            $this->authHeaders()
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_validates_max_size(): void
    {
        $file = UploadedFile::fake()->image('big.jpg')->size(5000); // 5MB > 4MB limit

        $response = $this->postJson(
            "/api/v1/users/{$this->user->ulid}/avatar",
            ['avatar' => $file],
            $this->authHeaders()
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_unauthenticated_user_cannot_upload_avatar(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson(
            "/api/v1/users/{$this->user->ulid}/avatar",
            ['avatar' => $file]
        );

        $response->assertUnauthorized();
    }

    public function test_user_can_delete_avatar(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->postJson("/api/v1/users/{$this->user->ulid}/avatar", ['avatar' => $file], $this->authHeaders());

        $response = $this->deleteJson(
            "/api/v1/users/{$this->user->ulid}/avatar",
            [],
            $this->authHeaders()
        );

        $response->assertNoContent();
        $this->assertCount(0, $this->user->fresh()->getMedia('avatar'));
    }

    public function test_unauthenticated_user_cannot_delete_avatar(): void
    {
        $response = $this->deleteJson("/api/v1/users/{$this->user->ulid}/avatar");

        $response->assertUnauthorized();
    }

    public function test_upload_requires_avatar_field(): void
    {
        $response = $this->postJson(
            "/api/v1/users/{$this->user->ulid}/avatar",
            [],
            $this->authHeaders()
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_user_without_permission_cannot_upload_avatar_for_another_user(): void
    {
        $other = User::factory()->create();
        $unprivileged = User::factory()->create();
        $token = $unprivileged->createToken('test')->accessToken;

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson(
            "/api/v1/users/{$other->ulid}/avatar",
            ['avatar' => $file],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertForbidden();
    }

    public function test_upload_returns_404_for_nonexistent_user(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson(
            '/api/v1/users/99999/avatar',
            ['avatar' => $file],
            $this->authHeaders()
        );

        $response->assertNotFound();
    }

    public function test_delete_returns_404_for_nonexistent_user(): void
    {
        $response = $this->deleteJson('/api/v1/users/99999/avatar', [], $this->authHeaders());

        $response->assertNotFound();
    }

    public function test_avatar_url_appears_in_user_show_after_upload(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->postJson("/api/v1/users/{$this->user->ulid}/avatar", ['avatar' => $file], $this->authHeaders());

        $response = $this->getJson("/api/v1/users/{$this->user->ulid}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('id', $this->user->ulid);

        $this->assertNotNull($response->json('avatar_url'));
    }

    public function test_user_without_avatar_uses_default_avatar_url(): void
    {
        $response = $this->getJson("/api/v1/users/{$this->user->ulid}", $this->authHeaders());

        $response->assertOk();

        $this->assertStringEndsWith(
            '/admin/custom/img/user-avatar.jpg',
            $response->json('avatar_url')
        );
    }
}
