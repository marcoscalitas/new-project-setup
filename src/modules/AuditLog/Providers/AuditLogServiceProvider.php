<?php

namespace Modules\AuditLog\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AuditLog\Models\AuditLog;
use Modules\AuditLog\Policies\AuditLogPolicy;
use Modules\AuditLog\Services\AuditLogExportService;
use Modules\AuditLog\Services\SpatieAuditLogger;
use Shared\Contracts\AuditLog\AuditLogger;
use Shared\Contracts\Export\ExportRegistry;

class AuditLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditLogExportService::class);
        $this->app->bind(AuditLogger::class, SpatieAuditLogger::class);
    }

    public function boot(): void
    {
        if (is_dir($migrations = __DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }

        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        app(ExportRegistry::class)->register(app(AuditLogExportService::class));

        if (is_dir($views = __DIR__.'/../Resources/views')) {
            $this->loadViewsFrom($views, 'auditlog');
        }

        if (file_exists($api = __DIR__.'/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
    }
}
