<?php

namespace Tests\Unit\Core;

use InvalidArgumentException;
use Modules\Core\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_creates_valid_email(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user@example.com', $email->value);
    }

    public function test_normalizes_to_lowercase(): void
    {
        $email = new Email('USER@EXAMPLE.COM');

        $this->assertSame('user@example.com', $email->value);
    }

    public function test_trims_whitespace(): void
    {
        $email = new Email('  user@example.com  ');

        $this->assertSame('user@example.com', $email->value);
    }

    public function test_throws_on_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('not-an-email');
    }

    public function test_throws_on_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('');
    }

    public function test_throws_on_missing_domain(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('user@');
    }

    public function test_to_string_returns_value(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user@example.com', (string) $email);
    }

    public function test_equals_returns_true_for_same_email(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('user@example.com');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_true_regardless_of_case(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('USER@EXAMPLE.COM');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_email(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('other@example.com');

        $this->assertFalse($a->equals($b));
    }

    public function test_domain_extracts_domain_part(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('example.com', $email->domain());
    }

    public function test_domain_works_with_subdomain(): void
    {
        $email = new Email('user@mail.example.co.uk');

        $this->assertSame('mail.example.co.uk', $email->domain());
    }
}
