<?php

use Illuminate\Support\Facades\Route;
use Modules\Export\Http\Controllers\Api\ExportController;

Route::middleware(['auth:api', 'verified', 'throttle:30,1'])->group(function () {
    Route::post('exports', [ExportController::class, 'export'])->name('api.exports.export');
    Route::get('exports/{ulid}/status', [ExportController::class, 'status'])->name('api.exports.status');
    Route::get('exports/{ulid}/download', [ExportController::class, 'download'])->name('api.exports.download');
});
