<?php

namespace Modules\Settings\Services;

use Shared\Contracts\Settings\SettingsReader;
use Shared\Contracts\Settings\SettingsWriter;

class SettingsService implements SettingsReader, SettingsWriter
{
    public function get(string $key, mixed $default = null): mixed
    {
        return app(SettingsReader::class)->get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        app(SettingsWriter::class)->set($key, $value);
    }

    public function forget(string $key): void
    {
        app(SettingsWriter::class)->forget($key);
    }

    public function all(): array
    {
        return app(SettingsReader::class)->all();
    }

    public function flush(): void
    {
        app(DatabaseSettingsReader::class)->flush();
    }
}
