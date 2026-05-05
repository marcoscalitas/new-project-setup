<?php

namespace Shared\Contracts\Settings;

interface SettingsWriter
{
    public function set(string $key, mixed $value): void;

    public function forget(string $key): void;
}
