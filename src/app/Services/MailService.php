<?php

namespace App\Services;

use App\Contracts\MailSenderInterface;
use App\Mail\GenericMailable;
use App\Mail\MailMessage;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Mail;

class MailService implements MailSenderInterface
{
    public function send(MailMessage $message): void
    {
        $this->buildPendingMail($message)->send(new GenericMailable($message));
    }

    public function queue(MailMessage $message, ?string $queue = null): void
    {
        $mailable = new GenericMailable($message);

        if ($queue !== null) {
            $mailable->onQueue($queue);
        }

        $this->buildPendingMail($message)->queue($mailable);
    }

    private function buildPendingMail(MailMessage $message): PendingMail
    {
        $mailer = Mail::to($message->to);

        if (!empty($message->cc)) {
            $mailer->cc($message->cc);
        }

        if (!empty($message->bcc)) {
            $mailer->bcc($message->bcc);
        }

        if (!empty($message->replyTo)) {
            $mailer->replyTo($message->replyTo);
        }

        return $mailer;
    }
}
