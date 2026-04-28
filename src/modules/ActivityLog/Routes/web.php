<?php

use Illuminate\Support\Facades\Route;
use Modules\ActivityLog\Http\Controllers\ActivityLogController;

Route::prefix('activity-log')
    ->middleware(['auth', 'verified', 'throttle:60,1'])
    ->group(function () {
        Route::get('/',     [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('/{id}', [ActivityLogController::class, 'show'])->name('activity-log.show');
    });
