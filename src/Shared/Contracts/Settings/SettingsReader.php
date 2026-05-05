<?php

namespace Shared\Contracts\Settings;

interface SettingsReader
{
    public function get(string $key, mixed $default = null): mixed;

    public function all(): array;
}
