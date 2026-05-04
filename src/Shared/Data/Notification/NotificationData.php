<?php

namespace Shared\Data\Notification;

final class NotificationData
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
        public readonly array $data = [],
        public readonly array $channels = ['database'],
    ) {}
}
