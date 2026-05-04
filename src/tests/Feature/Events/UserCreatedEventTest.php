<?php

namespace Tests\Feature\Events;

use Illuminate\Support\Facades\Event;
use Modules\User\Events\UserCreated;
use Modules\User\Models\User;
use Tests\TestCase;

class UserCreatedEventTest extends TestCase
{
    public function test_user_created_event_can_be_dispatched(): void
    {
        Event::fake([UserCreated::class]);

        $user = User::factory()->create();

        UserCreated::dispatch($user->ulid, $user->name, $user->email);

        Event::assertDispatched(UserCreated::class);
    }

    public function test_user_created_event_contains_user(): void
    {
        Event::fake([UserCreated::class]);

        $user = User::factory()->create();

        UserCreated::dispatch($user->ulid, $user->name, $user->email);

        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->userUlid === $user->ulid && $event->userEmail === $user->email;
        });
    }

    public function test_user_created_event_is_dispatchable(): void
    {
        $user = User::factory()->create();

        $event = new UserCreated($user->ulid, $user->name, $user->email);

        $this->assertEquals($user->ulid, $event->userUlid);
        $this->assertEquals($user->name, $event->userName);
        $this->assertEquals($user->email, $event->userEmail);
    }
}
