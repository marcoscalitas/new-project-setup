<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MailMessage $mailMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailMessage->subject,
            from: $this->mailMessage->fromEmail
                ? new \Illuminate\Mail\Mailables\Address(
                    $this->mailMessage->fromEmail,
                    $this->mailMessage->fromName,
                )
                : null,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->mailMessage->view,
            with: $this->mailMessage->data,
        );
    }

    public function attachments(): array
    {
        return array_map(
            fn(array $attachment) => \Illuminate\Mail\Mailables\Attachment::fromPath($attachment['path'])
                ->withMime($attachment['options']['mime'] ?? null)
                ->as($attachment['options']['as'] ?? null),
            $this->mailMessage->attachments,
        );
    }
}
