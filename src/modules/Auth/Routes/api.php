<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\AuthController;

// Auth routes — with rate limiting to prevent brute force
Route::prefix('auth')->group(function () {
    // 5 requests per minute for brute force protection
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('login',                [AuthController::class, 'login'])->name('api.auth.login');
        Route::post('two-factor-challenge', [AuthController::class, 'twoFactorChallenge'])->name('api.auth.twoFactorChallenge');
        Route::post('forgot-password',      [AuthController::class, 'forgotPassword'])->name('api.auth.forgotPassword');
    });

    // 3 requests per minute (more restrictive)
    Route::middleware('throttle:3,1')->group(function () {
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.resetPassword');
        Route::post('email/resend',   [AuthController::class, 'resendVerificationEmail'])->name('api.auth.email.resend');
    });

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    });
});
