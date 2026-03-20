<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->group(function () {
        // Admin web routes
    });
