<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Modules\Auth\Events\UserCreated;
use Modules\Auth\Listeners\LogUserCreation;
use Modules\Auth\Listeners\SendWelcomeEmail;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(UserCreated::class, [SendWelcomeEmail::class, 'handle']);
        Event::listen(UserCreated::class, [LogUserCreation::class, 'handle']);

        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        Route::prefix('api')->middleware('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
