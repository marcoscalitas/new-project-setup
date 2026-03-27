<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::prefix('notifications')
    ->middleware(['auth', 'throttle:60,1'])
    ->group(function () {
        Route::get('/',              [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread',        [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::get('/{id}',          [NotificationController::class, 'show'])->name('notifications.show');
        Route::patch('/{id}/read',   [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/read-all',     [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
        Route::delete('/{id}',       [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });
