<?php

namespace App\Mail;

use InvalidArgumentException;

readonly class MailMessage
{
    public function __construct(
        public string|array $to,
        public string $subject,
        public string $view,
        public array $data = [],
        public array $cc = [],
        public array $bcc = [],
        public array $replyTo = [],
        public array $attachments = [],
        public ?string $fromEmail = null,
        public ?string $fromName = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->to)) {
            throw new InvalidArgumentException('The recipient (to) is required.');
        }

        if (empty($this->subject)) {
            throw new InvalidArgumentException('The subject is required.');
        }

        if (empty($this->view)) {
            throw new InvalidArgumentException('The view is required.');
        }
    }

    public static function make(
        string|array $to,
        string $subject,
        string $view,
        array $data = [],
        array $cc = [],
        array $bcc = [],
        array $replyTo = [],
        array $attachments = [],
        ?string $fromEmail = null,
        ?string $fromName = null,
    ): self {
        return new self(
            to: $to,
            subject: $subject,
            view: $view,
            data: $data,
            cc: $cc,
            bcc: $bcc,
            replyTo: $replyTo,
            attachments: $attachments,
            fromEmail: $fromEmail,
            fromName: $fromName,
        );
    }
}
