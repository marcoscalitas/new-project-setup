<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\Setting;
use Shared\Contracts\Settings\SettingsWriter;

class DatabaseSettingsWriter implements SettingsWriter
{
    public function __construct(private readonly DatabaseSettingsReader $reader) {}

    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);

        $this->reader->flush();
    }

    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();

        $this->reader->flush();
    }
}
