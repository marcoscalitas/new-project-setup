<?php

namespace Modules\Auth\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Modules\Auth\Events\UserCreated;
use Modules\User\Models\User;

class AuthService
{
    /**
     * Authenticate a user and return an access token.
     * If the user has 2FA enabled, returns a pending token instead.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ['error' => 'Credenciais inválidas.', 'status' => 401];
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $pendingToken = Str::uuid()->toString();
            Cache::put("2fa_pending:{$pendingToken}", $user->id, now()->addMinutes(5));

            return ['two_factor' => true, 'two_factor_token' => $pendingToken, 'status' => 200];
        }

        $token = $user->createToken('api-token')->accessToken;

        return ['token' => $token, 'user' => $user, 'status' => 200];
    }

    /**
     * Complete the 2FA challenge and return an access token.
     */
    public function twoFactorChallenge(string $pendingToken, ?string $code, ?string $recoveryCode): array
    {
        $userId = Cache::get("2fa_pending:{$pendingToken}");

        if (!$userId) {
            return ['error' => 'Token inválido ou expirado.', 'status' => 422];
        }

        $user = User::find($userId);

        if (!$user) {
            return ['error' => 'Utilizador não encontrado.', 'status' => 422];
        }

        if ($code) {
            $provider = app(TwoFactorAuthenticationProvider::class);

            if (!$provider->verify(decrypt($user->two_factor_secret), $code)) {
                return ['error' => 'Código inválido.', 'status' => 422];
            }
        } elseif ($recoveryCode) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            $index = array_search($recoveryCode, $recoveryCodes, true);

            if ($index === false) {
                return ['error' => 'Código de recuperação inválido.', 'status' => 422];
            }

            // Invalidate used recovery code
            $recoveryCodes[$index] = Str::random(10) . '-' . Str::random(10);
            $user->forceFill(['two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes))])->save();
        } else {
            return ['error' => 'Código obrigatório.', 'status' => 422];
        }

        Cache::forget("2fa_pending:{$pendingToken}");

        $token = $user->createToken('api-token')->accessToken;

        return ['token' => $token, 'user' => $user, 'status' => 200];
    }

    /**
     * Register a new user and return an access token.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        UserCreated::dispatch($user);
        event(new Registered($user));

        $token = $user->createToken('api-token')->accessToken;

        return ['token' => $token, 'user' => $user, 'status' => 201];
    }

    /**
     * Revoke the current user's access token.
     */
    public function logout(User $user): void
    {
        $user->token()->revoke();
    }

    /**
     * Send a password reset link to the given email.
     */
    public function forgotPassword(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
        });
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerificationEmail(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }
}
