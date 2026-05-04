<?php

namespace Tests\Unit;

use Modules\Notification\Notifications\ActivityNotification;
use Modules\Notification\Services\LaravelNotifier;
use PHPUnit\Framework\TestCase;
use Shared\Contracts\Notification\Notifier;
use Shared\Data\Notification\NotificationData;

class LaravelNotifierTest extends TestCase
{
    private LaravelNotifier $notifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notifier = new LaravelNotifier();
    }

    public function test_implements_notifier_contract(): void
    {
        $this->assertInstanceOf(Notifier::class, $this->notifier);
    }

    public function test_send_calls_notify_on_notifiable(): void
    {
        $data     = new NotificationData(type: 'test', message: 'Test notification', data: ['url' => '/test']);
        $notified = null;

        $notifiable = new class($notified) {
            public function __construct(public mixed &$received) {}
            public function notify(mixed $notification): void { $this->received = $notification; }
            public function notifyNow(mixed $_): void {}
        };

        $this->notifier->send($notifiable, $data);

        $this->assertInstanceOf(ActivityNotification::class, $notifiable->received);
        $this->assertSame([
            'type'    => 'test',
            'message' => 'Test notification',
            'data'    => ['url' => '/test'],
        ], $notifiable->received->toArray($notifiable));
    }

    public function test_send_now_calls_notify_now_on_notifiable(): void
    {
        $data     = new NotificationData(type: 'test_now', message: 'Test notification now');
        $notified = null;

        $notifiable = new class($notified) {
            public function __construct(public mixed &$received) {}
            public function notify(mixed $_): void {}
            public function notifyNow(mixed $notification): void { $this->received = $notification; }
        };

        $this->notifier->sendNow($notifiable, $data);

        $this->assertInstanceOf(ActivityNotification::class, $notifiable->received);
        $this->assertSame([
            'type'    => 'test_now',
            'message' => 'Test notification now',
            'data'    => [],
        ], $notifiable->received->toArray($notifiable));
    }
}
