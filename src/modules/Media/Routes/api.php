<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;

Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->group(function () {
    Route::get('media',        [MediaController::class, 'index'])->name('api.media.index');
    Route::get('media/{id}',   [MediaController::class, 'show'])->name('api.media.show');
    Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('api.media.destroy');
});
