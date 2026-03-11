<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingController;

Route::prefix('settings')
    ->middleware(['web', 'auth'])
    ->group(function () {
        // Settings web routes
    });
