<?php

namespace Modules\Identity\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Modules\User\Events\UserCreated;
use Modules\Identity\Listeners\LogUserCreation;
use Modules\Identity\Listeners\SendEmailVerificationOnUserCreated;
use Modules\Identity\Listeners\SendWelcomeEmail;
use Modules\User\Models\User;

class IdentityServiceProvider extends ServiceProvider
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
        if (is_dir($views = __DIR__ . '/../Resources/views')) {
            $this->loadViewsFrom($views, 'auth');
        }

        VerifyEmail::createUrlUsing(function (User $notifiable) {
            return URL::temporarySignedRoute(
                'verification.activate',
                now()->addMinutes(config('auth.verification.expire', 60)),
                ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
            );
        });

        Event::listen(UserCreated::class, [SendEmailVerificationOnUserCreated::class, 'handle']);
        Event::listen(UserCreated::class, [SendWelcomeEmail::class, 'handle']);
        Event::listen(UserCreated::class, [LogUserCreation::class, 'handle']);

        if (file_exists($web = __DIR__ . '/../Routes/web.php')) {
            Route::middleware('web')->group($web);
        }
        if (file_exists($api = __DIR__ . '/../Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($api);
        }
        if (is_dir($migrations = __DIR__ . '/../Database/Migrations')) {
            $this->loadMigrationsFrom($migrations);
        }

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
