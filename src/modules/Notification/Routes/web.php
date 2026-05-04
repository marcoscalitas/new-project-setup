<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Web\NotificationController;

Route::middleware(['auth', 'verified'])->prefix('notifications')->group(function () {
    Route::get('/{id}/redirect', [NotificationController::class, 'redirect'])->name('notifications.redirect');
});
