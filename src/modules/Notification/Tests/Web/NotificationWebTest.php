<?php

namespace Modules\Notification\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Modules\User\Models\User;
use Tests\TestCase;

class NotificationWebTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function createNotification(User $user, array $data = []): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id'              => Str::uuid()->toString(),
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => array_merge(['message' => 'Test notification'], $data),
        ]);
    }

    // == LIST ==

    public function test_authenticated_user_can_list_notifications(): void
    {
        $this->createNotification($this->user);
        $this->createNotification($this->user);

        $response = $this->actingAs($this->user)
            ->getJson('/notifications');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_unauthenticated_user_cannot_list_notifications(): void
    {
        $response = $this->getJson('/notifications');

        $response->assertUnauthorized();
    }

    public function test_user_only_sees_own_notifications(): void
    {
        $other = User::factory()->create();
        $this->createNotification($this->user);
        $this->createNotification($other);

        $response = $this->actingAs($this->user)
            ->getJson('/notifications');

        $response->assertOk()
            ->assertJsonCount(1);
    }

    // == UNREAD ==

    public function test_user_can_list_unread_notifications(): void
    {
        $this->createNotification($this->user);
        $read = $this->createNotification($this->user);
        $read->markAsRead();

        $response = $this->actingAs($this->user)
            ->getJson('/notifications/unread');

        $response->assertOk()
            ->assertJsonCount(1);
    }

    // == SHOW ==

    public function test_user_can_view_notification(): void
    {
        $notification = $this->createNotification($this->user);

        $response = $this->actingAs($this->user)
            ->getJson("/notifications/{$notification->id}");

        $response->assertOk()
            ->assertJsonPath('id', $notification->id)
            ->assertJsonPath('data.message', 'Test notification');
    }

    // == MARK AS READ ==

    public function test_user_can_mark_notification_as_read(): void
    {
        $notification = $this->createNotification($this->user);

        $response = $this->actingAs($this->user)
            ->patchJson("/notifications/{$notification->id}/read");

        $response->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    // == MARK ALL AS READ ==

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->createNotification($this->user);
        $this->createNotification($this->user);

        $response = $this->actingAs($this->user)
            ->postJson('/notifications/read-all');

        $response->assertOk();
        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    // == DESTROY ==

    public function test_user_can_delete_notification(): void
    {
        $notification = $this->createNotification($this->user);

        $response = $this->actingAs($this->user)
            ->deleteJson("/notifications/{$notification->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
