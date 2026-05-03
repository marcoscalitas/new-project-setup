<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Api\NotificationController;

// Rate limiting: 60 requests/minute for authenticated users
Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->prefix('notifications')->group(function () {
    Route::get('/',            [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::get('/unread',      [NotificationController::class, 'unread'])->name('api.notifications.unread');
    Route::get('/{id}',        [NotificationController::class, 'show'])->name('api.notifications.show');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/read-all',   [NotificationController::class, 'markAllAsRead'])->name('api.notifications.readAll');
    Route::delete('/{id}',     [NotificationController::class, 'destroy'])->name('api.notifications.destroy');
});
