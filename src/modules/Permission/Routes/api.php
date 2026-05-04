<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\Api\PermissionController;
use Modules\Permission\Http\Controllers\Api\RoleController;

// Rate limiting: 60 requests/minute for authenticated users
Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->group(function () {
    Route::get('roles/trashed', [RoleController::class, 'trashed'])->name('api.roles.trashed');
    Route::patch('roles/{ulid}/restore', [RoleController::class, 'restore'])->name('api.roles.restore');
    Route::apiResource('roles', RoleController::class)->names('api.roles');

    Route::get('permissions/trashed', [PermissionController::class, 'trashed'])->name('api.permissions.trashed');
    Route::patch('permissions/{ulid}/restore', [PermissionController::class, 'restore'])->name('api.permissions.restore');
    Route::apiResource('permissions', PermissionController::class)->names('api.permissions');
});
