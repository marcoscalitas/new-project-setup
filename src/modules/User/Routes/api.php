<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

// Rate limiting: 60 requests/minute for authenticated users
Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->group(function () {
    Route::apiResource('users', UserController::class)->names('api.users')->parameters(['users' => 'id']);

    Route::post('users/{id}/avatar', [UserController::class, 'uploadAvatar'])->name('api.users.avatar.upload');
    Route::delete('users/{id}/avatar', [UserController::class, 'deleteAvatar'])->name('api.users.avatar.delete');
    Route::get('users/{id}/activity', [UserController::class, 'activity'])->name('api.users.activity');
});
