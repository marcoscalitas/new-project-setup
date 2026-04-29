<?php

namespace Tests\Unit;

use Modules\Core\Contracts\NotificationSenderInterface;
use Modules\Notification\Services\NotificationDispatcher;
use PHPUnit\Framework\TestCase;

class NotificationDispatcherTest extends TestCase
{
    private NotificationDispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new NotificationDispatcher();
    }

    public function test_implements_notification_sender_interface(): void
    {
        $this->assertInstanceOf(NotificationSenderInterface::class, $this->dispatcher);
    }

    public function test_send_calls_notify_on_notifiable(): void
    {
        $notification = new \stdClass();
        $notified     = null;

        $notifiable = new class($notified) {
            public function __construct(public mixed &$received) {}
            public function notify(mixed $notification): void { $this->received = $notification; }
            public function notifyNow(mixed $_): void {}
        };

        $this->dispatcher->send($notifiable, $notification);

        $this->assertSame($notification, $notifiable->received);
    }

    public function test_send_now_calls_notify_now_on_notifiable(): void
    {
        $notification = new \stdClass();
        $notified     = null;

        $notifiable = new class($notified) {
            public function __construct(public mixed &$received) {}
            public function notify(mixed $_): void {}
            public function notifyNow(mixed $notification): void { $this->received = $notification; }
        };

        $this->dispatcher->sendNow($notifiable, $notification);

        $this->assertSame($notification, $notifiable->received);
    }
}
