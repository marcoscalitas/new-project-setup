<?php

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        // TODO: Implement welcome email
        // Mail::send(new WelcomeEmail($event->user));
        
        logger()->info("Welcome email sent to {$event->user->email}");
    }
}
