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

    private function createNotification(array $overrides = []): DatabaseNotification
    {
        return DatabaseNotification::create(array_merge([
            'id' => (string) Str::ulid(),
            'type' => 'Modules\\Notification\\Notifications\\ActivityNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'type' => 'user_updated',
                'message' => 'User updated: user@example.com',
                'data' => ['url' => route('users.index')],
            ],
        ], $overrides));
    }

    public function test_dropdown_uses_template_notification_cards(): void
    {
        $this->createNotification();

        $response = $this->actingAs($this->user)->get('/');

        $response->assertOk()
            ->assertSee('dropdown-notification')
            ->assertSee(__('ui.today'))
            ->assertSee(__('ui.all'))
            ->assertSee(__('ui.unread'))
            ->assertSee(route('notifications.index'))
            ->assertSee('bg-primary-500/10')
            ->assertSee('User updated')
            ->assertSee('user@example.com')
            ->assertSee(route('notifications.redirect', $this->user->notifications()->first()->id));
    }

    public function test_index_lists_notifications_with_read_and_unread_states(): void
    {
        $this->createNotification([
            'data' => ['message' => 'Unread notification: first'],
            'read_at' => null,
        ]);
        $this->createNotification([
            'data' => ['message' => 'Read notification: second'],
            'read_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('notifications.index'));

        $response->assertOk()
            ->assertViewIs('notification::notifications.index')
            ->assertSee('Unread notification')
            ->assertSee('Read notification')
            ->assertSee('bg-primary-500/10')
            ->assertSee('bg-white');
    }

    public function test_index_can_filter_unread_notifications(): void
    {
        $this->createNotification([
            'data' => ['message' => 'Unread notification: first'],
            'read_at' => null,
        ]);
        $this->createNotification([
            'data' => ['message' => 'Read notification: second'],
            'read_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('notifications.index', ['filter' => 'unread']));

        $response->assertOk()
            ->assertSee('Unread notification');

        $messages = collect($response->viewData('notifications')->items())
            ->pluck('data')
            ->pluck('message')
            ->all();

        $this->assertContains('Unread notification: first', $messages);
        $this->assertNotContains('Read notification: second', $messages);
    }

    public function test_clicking_notification_redirects_to_nested_url_and_marks_it_read(): void
    {
        $notification = $this->createNotification([
            'data' => [
                'message' => 'User updated: user@example.com',
                'data' => ['url' => route('users.index')],
            ],
        ]);

        $response = $this->actingAs($this->user)->get(route('notifications.redirect', $notification->id));

        $response->assertRedirect(route('users.index'));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_clicking_notification_redirects_to_legacy_url(): void
    {
        $notification = $this->createNotification([
            'data' => [
                'message' => 'Legacy notification',
                'url' => route('home'),
            ],
        ]);

        $response = $this->actingAs($this->user)->get(route('notifications.redirect', $notification->id));

        $response->assertRedirect(route('home'));
    }

    public function test_user_cannot_redirect_another_users_notification(): void
    {
        $other = User::factory()->create();
        $notification = $this->createNotification(['notifiable_id' => $other->id]);

        $this->actingAs($this->user)
            ->get(route('notifications.redirect', $notification->id))
            ->assertNotFound();
    }
}
