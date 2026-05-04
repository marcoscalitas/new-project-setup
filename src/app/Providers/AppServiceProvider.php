<?php

namespace App\Providers;

use App\Contracts\MailSenderInterface;
use App\Services\MailService;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Shared\Media\Contracts\MediaRemover;
use Shared\Media\Contracts\MediaUploader;
use Shared\Media\Services\SpatieMediaRemover;
use Shared\Media\Services\SpatieMediaUploader;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MailSenderInterface::class, MailService::class);
        $this->app->bind(MediaUploader::class, SpatieMediaUploader::class);
        $this->app->bind(MediaRemover::class, SpatieMediaRemover::class);

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Blade::anonymousComponentPath(resource_path('views/admin/components'), 'admin');

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'Bearer')
                );
            });
    }
}
