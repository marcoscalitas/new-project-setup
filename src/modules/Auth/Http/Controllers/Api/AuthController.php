<?php

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\ResetPasswordRequest;
use Modules\Auth\Http\Requests\TwoFactorChallengeRequest;
use Modules\Auth\Http\Resources\AuthResource;
use Modules\Auth\Services\AuthService;

class AuthController
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->only('email', 'password'));

        if (isset($result['error'])) {
            $body = ['message' => $result['error']];
            if (isset($result['resend_url'])) {
                $body['resend_url'] = $result['resend_url'];
            }
            return response()->json($body, $result['status']);
        }

        if (isset($result['two_factor'])) {
            return response()->json([
                'two_factor'       => true,
                'two_factor_token' => $result['two_factor_token'],
            ], $result['status']);
        }

        return response()->json([
            'token' => $result['token'],
            'user'  => new AuthResource($result['user']),
        ], $result['status']);
    }

    public function twoFactorChallenge(TwoFactorChallengeRequest $request): JsonResponse
    {
        $result = $this->authService->twoFactorChallenge(
            $request->input('two_factor_token'),
            $request->input('code'),
            $request->input('recovery_code'),
        );

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json([
            'token' => $result['token'],
            'user'  => new AuthResource($result['user']),
        ], $result['status']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => __('auth.logout_success')]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('email'));

        // Always returns 200 to prevent email enumeration (OWASP)
        return response()->json(['message' => __('auth.password_reset_link_sent')]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __('auth.password_reset_success')])
            : response()->json(['message' => __('auth.invalid_or_expired_token')], 400);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $this->authService->resendVerificationEmail($request->input('email'));

        return response()->json(['message' => __('auth.verification_email_resent')]);
    }
}
