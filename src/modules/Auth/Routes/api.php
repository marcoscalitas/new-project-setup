<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

// Auth routes — with rate limiting to prevent brute force
Route::prefix('auth')->group(function () {
    // 5 requests per minute for brute force protection
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('login',           [AuthController::class, 'login']);
        Route::post('register',        [AuthController::class, 'register']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    });

    // 3 requests per minute for password reset (more restrictive)
    Route::middleware('throttle:3,1')->group(function () {
        Route::post('reset-password',  [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
