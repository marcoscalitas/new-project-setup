<?php

use Shared\Contracts\Settings\SettingsReader;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsReader::class)->get($key, $default);
    }
}
