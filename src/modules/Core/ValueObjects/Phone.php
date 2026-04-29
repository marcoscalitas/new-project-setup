<?php

namespace Modules\Core\ValueObjects;

use InvalidArgumentException;

final class Phone
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) < 7 || strlen($digits) > 15) {
            throw new InvalidArgumentException("Invalid phone number: {$value}");
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function digits(): string
    {
        return preg_replace('/\D/', '', $this->value);
    }

    public function equals(self $other): bool
    {
        return $this->digits() === $other->digits();
    }
}
