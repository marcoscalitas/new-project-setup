<?php

namespace Modules\Settings\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Settings\Models\Setting;
use Modules\Settings\Policies\SettingPolicy;
use Modules\Settings\Services\DatabaseSettingsReader;
use Modules\Settings\Services\DatabaseSettingsWriter;
use Modules\Settings\Services\SettingsService;
use Shared\Contracts\Settings\SettingsReader;
use Shared\Contracts\Settings\SettingsWriter;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseSettingsReader::class);
        $this->app->singleton(SettingsReader::class, DatabaseSettingsReader::class);
        $this->app->singleton(SettingsWriter::class, DatabaseSettingsWriter::class);
        $this->app->singleton(SettingsService::class);

        require_once __DIR__.'/../helpers.php';
    }

    public function boot(): void
    {
        Gate::policy(Setting::class, SettingPolicy::class);

        if (file_exists($api = __DIR__.'/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }

        if (is_dir($migrations = __DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }
    }
}
