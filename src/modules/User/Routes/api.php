<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

// Rate limiting: 60 requests/minute for authenticated users
Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
    Route::apiResource('users', UserController::class);
});
