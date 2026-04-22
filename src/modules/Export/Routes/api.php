<?php

use Illuminate\Support\Facades\Route;
use Modules\Export\Http\Controllers\ExportController;

Route::middleware(['auth:api', 'verified', 'throttle:30,1'])->group(function () {
    Route::post('exports', [ExportController::class, 'export'])->name('api.exports.export');
    Route::get('exports/{uuid}/status', [ExportController::class, 'status'])->name('api.exports.status');
    Route::get('exports/{uuid}/download', [ExportController::class, 'download'])->name('api.exports.download');
});
