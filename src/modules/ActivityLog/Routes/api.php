<?php

use Illuminate\Support\Facades\Route;
use Modules\ActivityLog\Http\Controllers\Api\ActivityLogController;

Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->prefix('activity-log')->group(function () {
    Route::get('/',                    [ActivityLogController::class, 'index'])->name('api.activity-log.index');
    Route::get('/users/{userUlid}',    [ActivityLogController::class, 'forUser'])->name('api.activity-log.for-user');
    Route::get('/{id}',                [ActivityLogController::class, 'show'])->name('api.activity-log.show');
});
