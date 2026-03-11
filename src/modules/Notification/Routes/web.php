<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::prefix('notification')
    ->middleware(['web', 'auth'])
    ->group(function () {
        // Notification web routes
    });
