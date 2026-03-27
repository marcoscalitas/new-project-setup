<?php

namespace Tests\Feature\Events;

use Illuminate\Support\Facades\Event;
use Modules\Auth\Events\UserCreated;
use Modules\User\Models\User;
use Tests\TestCase;

class UserCreatedEventTest extends TestCase
{
    public function test_user_created_event_can_be_dispatched()
    {
        Event::fake();

        $user = User::factory()->create();

        UserCreated::dispatch($user);

        Event::assertDispatched(UserCreated::class);
    }

    public function test_user_created_event_contains_user()
    {
        Event::fake();

        $user = User::factory()->create();

        UserCreated::dispatch($user);

        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_user_created_event_is_dispatchable()
    {
        $user = User::factory()->create();

        // Just verify we can create and dispatch the event
        $event = new UserCreated($user);

        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($user->email, $event->user->email);
    }
}
