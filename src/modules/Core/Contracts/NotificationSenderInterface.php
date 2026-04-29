<?php

namespace Modules\Core\Contracts;

interface NotificationSenderInterface
{
    public function send(mixed $notifiable, mixed $notification): void;

    public function sendNow(mixed $notifiable, mixed $notification): void;
}
