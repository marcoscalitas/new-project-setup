<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Web\NotificationController;

Route::middleware(['auth', 'verified'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/{id}/redirect', [NotificationController::class, 'redirect'])->name('notifications.redirect');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});
