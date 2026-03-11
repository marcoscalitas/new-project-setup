<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

Route::prefix('auth')
    ->middleware(['web'])
    ->group(function () {
        // Auth web routes
    });
