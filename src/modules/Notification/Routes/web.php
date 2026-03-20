<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::prefix('notification')
    ->middleware(['auth'])
    ->group(function () {
        // Notification web routes
    });
