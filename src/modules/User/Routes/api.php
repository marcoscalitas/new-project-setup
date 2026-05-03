<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Api\UserController;

// Rate limiting: 60 requests/minute for authenticated users
Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->group(function () {
    Route::apiResource('users', UserController::class)->names('api.users');

    Route::post('users/{user}/avatar', [UserController::class, 'uploadAvatar'])->name('api.users.avatar.upload');
    Route::delete('users/{user}/avatar', [UserController::class, 'deleteAvatar'])->name('api.users.avatar.delete');
});
