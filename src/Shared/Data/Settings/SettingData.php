<?php

namespace Shared\Data\Settings;

final class SettingData
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
    ) {}
}
