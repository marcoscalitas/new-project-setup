<?php

namespace Modules\Notification\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Modules\User\Models\User;
use Tests\TestCase;

class NotificationTest extends TestCase
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
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    private function createNotification(array $overrides = []): DatabaseNotification
    {
        return DatabaseNotification::create(array_merge([
            'id'              => Str::uuid()->toString(),
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['message' => 'Test notification'],
        ], $overrides));
    }

    // == LIST ==

    public function test_authenticated_user_can_list_notifications(): void
    {
        $this->createNotification();
        $this->createNotification();

        $response = $this->getJson('/api/notifications', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_list_notifications(): void
    {
        $response = $this->getJson('/api/notifications');

        $response->assertUnauthorized();
    }

    public function test_user_only_sees_own_notifications(): void
    {
        $other = User::factory()->create();

        $this->createNotification();
        $this->createNotification(['notifiable_id' => $other->id]);

        $response = $this->getJson('/api/notifications', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // == UNREAD ==

    public function test_user_can_list_unread_notifications(): void
    {
        $this->createNotification();
        $this->createNotification(['read_at' => now()]);

        $response = $this->getJson('/api/notifications/unread', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(1, 'data');
    }

    // == SHOW ==

    public function test_user_can_view_notification(): void
    {
        $notification = $this->createNotification();

        $response = $this->getJson("/api/notifications/{$notification->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('id', $notification->id)
            ->assertJsonPath('data.message', 'Test notification');
    }

    public function test_view_notification_returns_404_for_invalid_id(): void
    {
        $response = $this->getJson('/api/notifications/' . Str::uuid(), $this->authHeaders());

        $response->assertNotFound();
    }

    // == MARK AS READ ==

    public function test_user_can_mark_notification_as_read(): void
    {
        $notification = $this->createNotification();

        $response = $this->patchJson("/api/notifications/{$notification->id}/read", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Notificação marcada como lida.');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_as_read_returns_404_for_invalid_id(): void
    {
        $response = $this->patchJson('/api/notifications/' . Str::uuid() . '/read', [], $this->authHeaders());

        $response->assertNotFound();
    }

    // == MARK ALL AS READ ==

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $n1 = $this->createNotification();
        $n2 = $this->createNotification();

        $response = $this->postJson('/api/notifications/read-all', [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Todas as notificações marcadas como lidas.');

        $this->assertNotNull($n1->fresh()->read_at);
        $this->assertNotNull($n2->fresh()->read_at);
    }

    // == DELETE ==

    public function test_user_can_delete_notification(): void
    {
        $notification = $this->createNotification();

        $response = $this->deleteJson("/api/notifications/{$notification->id}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
    }

    public function test_delete_notification_returns_404_for_invalid_id(): void
    {
        $response = $this->deleteJson('/api/notifications/' . Str::uuid(), [], $this->authHeaders());

        $response->assertNotFound();
    }

    public function test_deleted_notification_is_not_listed(): void
    {
        $notification = $this->createNotification();
        $this->deleteJson("/api/notifications/{$notification->id}", [], $this->authHeaders());

        $response = $this->getJson('/api/notifications', $this->authHeaders());

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($notification->id, $ids);
    }

    public function test_user_cannot_view_another_users_notification(): void
    {
        $other         = User::factory()->create();
        $notification  = DatabaseNotification::create([
            'id'              => Str::uuid()->toString(),
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $other->id,
            'data'            => ['message' => 'Private'],
        ]);

        // Service scopes notifications by notifiable_id, so another user's
        // notification is not found rather than forbidden (safe by design)
        $this->getJson("/api/notifications/{$notification->id}", $this->authHeaders())
            ->assertNotFound();
    }

    public function test_user_cannot_delete_another_users_notification(): void
    {
        $other         = User::factory()->create();
        $notification  = DatabaseNotification::create([
            'id'              => Str::uuid()->toString(),
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $other->id,
            'data'            => ['message' => 'Private'],
        ]);

        // Service scopes notifications by notifiable_id, so another user's
        // notification is not found rather than forbidden (safe by design)
        $this->deleteJson("/api/notifications/{$notification->id}", [], $this->authHeaders())
            ->assertNotFound();
    }
}
