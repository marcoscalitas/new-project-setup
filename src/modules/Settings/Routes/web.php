<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

Route::middleware(['auth', 'verified', 'throttle:60,1'])->prefix('settings')->group(function () {
    Route::get('/',          [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/{key}',     [SettingsController::class, 'show'])->name('settings.show');
    Route::put('/{key}',     [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/{key}',  [SettingsController::class, 'destroy'])->name('settings.destroy');
});
