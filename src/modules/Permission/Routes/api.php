<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\PermissionController;
use Modules\Permission\Http\Controllers\RoleController;

// Rate limiting: 60 requests/minute for authenticated users
Route::middleware(['auth:api', 'throttle:60,1'])->prefix('permissions')->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
});
