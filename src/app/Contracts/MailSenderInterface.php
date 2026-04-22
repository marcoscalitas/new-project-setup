<?php

namespace App\Contracts;

use App\Mail\MailMessage;

interface MailSenderInterface
{
    public function send(MailMessage $message): void;

    public function queue(MailMessage $message, ?string $queue = null): void;
}
