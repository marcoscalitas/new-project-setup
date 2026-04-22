<?php

namespace Tests\Unit;

use App\Mail\MailMessage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MailMessageTest extends TestCase
{
    // --- construction & defaults ---

    public function test_make_creates_instance_with_required_fields(): void
    {
        $msg = MailMessage::make(
            to: 'user@example.com',
            subject: 'Hello',
            view: 'emails.hello',
        );

        $this->assertSame('user@example.com', $msg->to);
        $this->assertSame('Hello', $msg->subject);
        $this->assertSame('emails.hello', $msg->view);
    }

    public function test_optional_fields_default_to_empty(): void
    {
        $msg = MailMessage::make(
            to: 'user@example.com',
            subject: 'Hello',
            view: 'emails.hello',
        );

        $this->assertSame([], $msg->data);
        $this->assertSame([], $msg->cc);
        $this->assertSame([], $msg->bcc);
        $this->assertSame([], $msg->replyTo);
        $this->assertSame([], $msg->attachments);
        $this->assertNull($msg->fromEmail);
        $this->assertNull($msg->fromName);
    }

    public function test_make_with_all_optional_fields(): void
    {
        $msg = MailMessage::make(
            to: 'user@example.com',
            subject: 'Hello',
            view: 'emails.hello',
            data: ['key' => 'value'],
            cc: ['cc@example.com'],
            bcc: ['bcc@example.com'],
            replyTo: ['reply@example.com'],
            attachments: [['path' => '/tmp/file.pdf', 'options' => []]],
            fromEmail: 'noreply@example.com',
            fromName: 'No Reply',
        );

        $this->assertSame(['key' => 'value'], $msg->data);
        $this->assertSame(['cc@example.com'], $msg->cc);
        $this->assertSame(['bcc@example.com'], $msg->bcc);
        $this->assertSame(['reply@example.com'], $msg->replyTo);
        $this->assertSame('noreply@example.com', $msg->fromEmail);
        $this->assertSame('No Reply', $msg->fromName);
    }

    public function test_to_accepts_array_of_recipients(): void
    {
        $msg = MailMessage::make(
            to: ['a@example.com', 'b@example.com'],
            subject: 'Hello',
            view: 'emails.hello',
        );

        $this->assertSame(['a@example.com', 'b@example.com'], $msg->to);
    }

    // --- validation ---

    public function test_throws_when_to_is_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('recipient');

        MailMessage::make(to: '', subject: 'Hello', view: 'emails.hello');
    }

    public function test_throws_when_to_is_empty_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('recipient');

        MailMessage::make(to: [], subject: 'Hello', view: 'emails.hello');
    }

    public function test_throws_when_subject_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('subject');

        MailMessage::make(to: 'user@example.com', subject: '', view: 'emails.hello');
    }

    public function test_throws_when_view_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('view');

        MailMessage::make(to: 'user@example.com', subject: 'Hello', view: '');
    }
}
