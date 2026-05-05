<?php

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\Setting;
use Shared\Contracts\Settings\SettingsReader;

class DatabaseSettingsReader implements SettingsReader
{
    private const CACHE_KEY = 'settings.all';

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
