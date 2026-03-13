<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;                           
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\ResetPasswordRequest;
use Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * Handle a login request.
     */
    public function login(LoginRequest $request)
    {
        //
    }

    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request)
    {
        //
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        //
    }

    /**
     * Handle a forgot password request.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        //
    }

    /**
     * Handle a reset password request.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        //
    }
}
