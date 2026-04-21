<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
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
            return response()->json(['message' => $result['error']], $result['status']);
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

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'token' => $result['token'],
            'user'  => new AuthResource($result['user']),
        ], $result['status']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('email'));

        // Sempre retorna 200 para evitar enumeração de e-mails (OWASP)
        return response()->json(['message' => 'Link de recuperação enviado.']);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Senha redefinida com sucesso.'])
            : response()->json(['message' => 'Token inválido ou expirado.'], 400);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mail já verificado.'], 422);
        }

        $this->authService->resendVerificationEmail($request->user());

        return response()->json(['message' => 'E-mail de verificação reenviado.']);
    }
}
