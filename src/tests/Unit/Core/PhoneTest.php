<?php

namespace Tests\Unit\Core;

use InvalidArgumentException;
use Modules\Core\ValueObjects\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_creates_valid_phone_with_plain_digits(): void
    {
        $phone = new Phone('1234567');

        $this->assertSame('1234567', $phone->value);
    }

    public function test_accepts_formatted_phone_with_spaces_and_dashes(): void
    {
        $phone = new Phone('+351 912 345 678');

        $this->assertSame('+351 912 345 678', $phone->value);
    }

    public function test_accepts_international_format(): void
    {
        $phone = new Phone('+1-800-555-0199');

        $this->assertSame('+1-800-555-0199', $phone->value);
    }

    public function test_throws_when_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Phone('12345');
    }

    public function test_throws_when_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Phone('1234567890123456');
    }

    public function test_throws_when_only_symbols(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Phone('+++---');
    }

    public function test_to_string_returns_original_value(): void
    {
        $phone = new Phone('+351 912 345 678');

        $this->assertSame('+351 912 345 678', (string) $phone);
    }

    public function test_digits_strips_non_digit_characters(): void
    {
        $phone = new Phone('+351 912 345 678');

        $this->assertSame('351912345678', $phone->digits());
    }

    public function test_equals_returns_true_for_same_digits(): void
    {
        $a = new Phone('+351912345678');
        $b = new Phone('+351 912 345 678');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_numbers(): void
    {
        $a = new Phone('1234567');
        $b = new Phone('7654321');

        $this->assertFalse($a->equals($b));
    }

    public function test_accepts_minimum_length_phone(): void
    {
        $phone = new Phone('1234567');

        $this->assertSame('1234567', $phone->digits());
    }

    public function test_accepts_maximum_length_phone(): void
    {
        $phone = new Phone('123456789012345');

        $this->assertSame('123456789012345', $phone->digits());
    }
}
