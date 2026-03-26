<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::middleware('auth:api')->prefix('notifications')->group(function () {
    Route::get('/',          [NotificationController::class, 'index']);
    Route::get('/unread',    [NotificationController::class, 'unread']);
    Route::get('/{id}',      [NotificationController::class, 'show']);
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}',   [NotificationController::class, 'destroy']);
});
