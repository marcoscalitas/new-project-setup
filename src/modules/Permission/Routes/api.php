<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\PermissionController;
use Modules\Permission\Http\Controllers\RoleController;

// Rate limiting: 60 requests/minute for authenticated users
Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->group(function () {
    Route::apiResource('roles', RoleController::class)->names('api.roles');
    Route::apiResource('permissions', PermissionController::class)->names('api.permissions');
});
