<?php

namespace Tests\Unit\Core;

use Modules\Core\Enums\Gender;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    public function test_male_value_is_string_male(): void
    {
        $this->assertSame('male', Gender::Male->value);
    }

    public function test_female_value_is_string_female(): void
    {
        $this->assertSame('female', Gender::Female->value);
    }

    public function test_other_value_is_string_other(): void
    {
        $this->assertSame('other', Gender::Other->value);
    }

    public function test_male_label(): void
    {
        $this->assertSame('Male', Gender::Male->label());
    }

    public function test_female_label(): void
    {
        $this->assertSame('Female', Gender::Female->label());
    }

    public function test_other_label(): void
    {
        $this->assertSame('Other', Gender::Other->label());
    }

    public function test_can_be_created_from_string_value(): void
    {
        $this->assertSame(Gender::Male, Gender::from('male'));
        $this->assertSame(Gender::Female, Gender::from('female'));
        $this->assertSame(Gender::Other, Gender::from('other'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(Gender::tryFrom('unknown'));
    }

    public function test_cases_returns_all_three_values(): void
    {
        $this->assertCount(3, Gender::cases());
    }
}
