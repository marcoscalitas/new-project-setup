<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Modules\User\Models\User;

class AuthService
{
    /**
     * Authenticate a user and return an access token.
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            return ['error' => 'Credenciais inválidas.', 'status' => 401];
        }

        $user  = Auth::user();
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
}
