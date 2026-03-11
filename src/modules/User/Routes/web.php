<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::prefix('user')
    ->middleware(['web', 'auth'])
    ->group(function () {
        // User web routes
    });
