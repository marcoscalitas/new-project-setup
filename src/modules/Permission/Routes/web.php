<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\RoleController;
use Modules\Permission\Http\Controllers\PermissionController;

Route::prefix('permission')
    ->middleware(['web', 'auth'])
    ->group(function () {
        // Permission web routes
    });
