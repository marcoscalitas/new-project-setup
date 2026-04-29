<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;

Route::middleware(['auth', 'verified', 'throttle:60,1'])->prefix('media')->group(function () {
    Route::get('/',        [MediaController::class, 'index'])->name('media.index');
    Route::get('/{id}',    [MediaController::class, 'show'])->name('media.show');
    Route::delete('/{id}', [MediaController::class, 'destroy'])->name('media.destroy');
});
