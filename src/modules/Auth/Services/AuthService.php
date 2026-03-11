<?php

namespace Modules\Auth\Services;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Authenticate a user.
     */
    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->accessToken;
        return ['user' =>  $user, 'token' => $token];
    }

    /**
     * Register a new user.
     */
    public function register(array $data)
    {
        //
    }

    /**
     * Logout the current user.
     */
    public function logout()
    {
        //
    }

    /**
     * Send a password reset link.
     */
    public function forgotPassword(string $email)
    {
        //
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(array $data)
    {
        //
    }
}