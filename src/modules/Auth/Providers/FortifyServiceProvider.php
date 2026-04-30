<?php

namespace Modules\Auth\Providers;

use Modules\Auth\Actions\CreateNewUser;
use Modules\Auth\Actions\ResetUserPassword;
use Modules\Auth\Actions\UpdateUserPassword;
use Modules\Auth\Actions\UpdateUserProfileInformation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Modules\User\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return null;
            }

            if (!$user->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    'activation' => [__('auth.email_activation_sent')],
                ]);
            }

            return $user;
        });

        Fortify::loginView(fn () => view('auth::login'));
        Fortify::registerView(fn () => view('auth::register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth::forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth::reset-password', ['token' => $request->route('token'), 'email' => $request->email]));
        Fortify::verifyEmailView(fn () => view('auth::verify-email'));

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $email = urlencode($notifiable->getEmailForPasswordReset());
            $base  = rtrim(config('app.frontend_url', config('app.url')), '/');

            return "{$base}/reset-password/{$token}?email={$email}";
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('verification.send', function (Request $request) {
            $throttleSeconds = config('auth.verification.throttle', 60);
            return Limit::perMinutes((int) ceil($throttleSeconds / 60), 1)->by($request->user()?->id ?: $request->ip());
        });
    }
}
