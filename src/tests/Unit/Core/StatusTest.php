<?php

namespace Tests\Unit\Core;

use Modules\Core\Enums\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function test_active_value_is_string_active(): void
    {
        $this->assertSame('active', Status::Active->value);
    }

    public function test_inactive_value_is_string_inactive(): void
    {
        $this->assertSame('inactive', Status::Inactive->value);
    }

    public function test_active_label(): void
    {
        $this->assertSame('Active', Status::Active->label());
    }

    public function test_inactive_label(): void
    {
        $this->assertSame('Inactive', Status::Inactive->label());
    }

    public function test_active_is_active_returns_true(): void
    {
        $this->assertTrue(Status::Active->isActive());
    }

    public function test_inactive_is_active_returns_false(): void
    {
        $this->assertFalse(Status::Inactive->isActive());
    }

    public function test_can_be_created_from_string_value(): void
    {
        $status = Status::from('active');

        $this->assertSame(Status::Active, $status);
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $status = Status::tryFrom('unknown');

        $this->assertNull($status);
    }

    public function test_cases_returns_all_values(): void
    {
        $cases = Status::cases();

        $this->assertCount(2, $cases);
    }
}
