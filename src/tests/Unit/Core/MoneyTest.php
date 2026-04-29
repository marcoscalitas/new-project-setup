<?php

namespace Tests\Unit\Core;

use InvalidArgumentException;
use Modules\Core\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_creates_with_amount_in_cents(): void
    {
        $money = new Money(1000);

        $this->assertSame(1000, $money->amount);
        $this->assertSame('EUR', $money->currency);
    }

    public function test_creates_with_custom_currency(): void
    {
        $money = new Money(500, 'USD');

        $this->assertSame('USD', $money->currency);
    }

    public function test_throws_on_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(-1);
    }

    public function test_zero_amount_is_valid(): void
    {
        $money = new Money(0);

        $this->assertSame(0, $money->amount);
    }

    public function test_from_float_converts_to_cents(): void
    {
        $money = Money::fromFloat(10.00);

        $this->assertSame(1000, $money->amount);
    }

    public function test_from_float_rounds_correctly(): void
    {
        $money = Money::fromFloat(10.005);

        $this->assertSame(1001, $money->amount);
    }

    public function test_from_float_uses_custom_currency(): void
    {
        $money = Money::fromFloat(5.50, 'USD');

        $this->assertSame(550, $money->amount);
        $this->assertSame('USD', $money->currency);
    }

    public function test_to_float_converts_cents_to_decimal(): void
    {
        $money = new Money(1050);

        $this->assertSame(10.5, $money->toFloat());
    }

    public function test_formatted_returns_readable_string(): void
    {
        $money = new Money(1000);

        $this->assertSame('10.00 EUR', $money->formatted());
    }

    public function test_formatted_with_custom_currency(): void
    {
        $money = new Money(2550, 'USD');

        $this->assertSame('25.50 USD', $money->formatted());
    }

    public function test_add_sums_two_amounts(): void
    {
        $a = new Money(1000);
        $b = new Money(500);

        $result = $a->add($b);

        $this->assertSame(1500, $result->amount);
        $this->assertSame('EUR', $result->currency);
    }

    public function test_add_throws_on_different_currencies(): void
    {
        $a = new Money(1000, 'EUR');
        $b = new Money(500, 'USD');

        $this->expectException(InvalidArgumentException::class);

        $a->add($b);
    }

    public function test_subtract_deducts_amount(): void
    {
        $a = new Money(1000);
        $b = new Money(300);

        $result = $a->subtract($b);

        $this->assertSame(700, $result->amount);
    }

    public function test_subtract_throws_on_different_currencies(): void
    {
        $a = new Money(1000, 'EUR');
        $b = new Money(500, 'USD');

        $this->expectException(InvalidArgumentException::class);

        $a->subtract($b);
    }

    public function test_subtract_throws_when_result_is_negative(): void
    {
        $a = new Money(100);
        $b = new Money(500);

        $this->expectException(InvalidArgumentException::class);

        $a->subtract($b);
    }

    public function test_equals_returns_true_for_same_amount_and_currency(): void
    {
        $a = new Money(1000, 'EUR');
        $b = new Money(1000, 'EUR');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_amount(): void
    {
        $a = new Money(1000);
        $b = new Money(999);

        $this->assertFalse($a->equals($b));
    }

    public function test_equals_returns_false_for_different_currency(): void
    {
        $a = new Money(1000, 'EUR');
        $b = new Money(1000, 'USD');

        $this->assertFalse($a->equals($b));
    }

    public function test_add_is_immutable(): void
    {
        $a = new Money(1000);
        $b = new Money(500);

        $result = $a->add($b);

        $this->assertSame(1000, $a->amount);
        $this->assertNotSame($a, $result);
    }

    public function test_subtract_is_immutable(): void
    {
        $a = new Money(1000);
        $b = new Money(300);

        $result = $a->subtract($b);

        $this->assertSame(1000, $a->amount);
        $this->assertNotSame($a, $result);
    }
}
