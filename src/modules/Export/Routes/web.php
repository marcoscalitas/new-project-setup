<?php

use Illuminate\Support\Facades\Route;
use Modules\Export\Http\Controllers\ExportController;

Route::prefix('exports')
    ->middleware(['auth', 'verified', 'throttle:60,1'])
    ->group(function () {
        Route::get('/',                   [ExportController::class, 'index'])->name('exports.index');
        Route::post('/',                  [ExportController::class, 'export'])->name('exports.store');
        Route::get('/{uuid}/download',    [ExportController::class, 'download'])->name('exports.download');
    });
