<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingController;

Route::prefix('settings')
    ->middleware(['auth'])
    ->group(function () {
        // Settings web routes
    });
