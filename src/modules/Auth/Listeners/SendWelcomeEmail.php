<?php

namespace Modules\Auth\Listeners;

use App\Contracts\MailSenderInterface;
use App\Mail\MailMessage;
use Modules\Auth\Events\UserCreated;

class SendWelcomeEmail
{
    public function __construct(private MailSenderInterface $mail) {}

    public function handle(UserCreated $event): void
    {
        $this->mail->queue(
            MailMessage::make(
                to: $event->user->email,
                subject: 'Welcome to ' . config('app.name'),
                view: 'auth::emails.welcome',
                data: ['user' => $event->user],
            )
        );
    }
}

