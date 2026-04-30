<?php

namespace Modules\Auth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Auth\Events\UserCreated;
use Modules\User\Models\User;

class SendEmailVerificationOnUserCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserCreated $event): void
    {
        $user = User::where('ulid', $event->userUlid)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
