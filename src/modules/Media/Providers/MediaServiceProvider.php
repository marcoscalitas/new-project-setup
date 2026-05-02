<?php

namespace Modules\Media\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\FileUploadInterface;
use Modules\Media\Policies\MediaPolicy;
use Modules\Media\Services\MediaService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FileUploadInterface::class, MediaService::class);
    }

    public function boot(): void
    {
        if (is_dir($migrations = __DIR__ . '/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }

        Gate::policy(Media::class, MediaPolicy::class);

        if (file_exists($api = __DIR__ . '/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
    }
}
