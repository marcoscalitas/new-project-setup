<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->group(function () {
    Route::get('settings',        [SettingsController::class, 'index'])->name('api.settings.index');
    Route::get('settings/{key}',  [SettingsController::class, 'show'])->name('api.settings.show');
    Route::put('settings/{key}',  [SettingsController::class, 'update'])->name('api.settings.update');
    Route::delete('settings/{key}', [SettingsController::class, 'destroy'])->name('api.settings.destroy');
});
