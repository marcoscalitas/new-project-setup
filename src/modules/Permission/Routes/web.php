<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\RoleController;
use Modules\Permission\Http\Controllers\PermissionController;

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::prefix('roles')->group(function () {
        Route::get('/',        [RoleController::class, 'index'])->name('roles.index');
        Route::post('/',       [RoleController::class, 'store'])->name('roles.store');
        Route::get('/{id}',    [RoleController::class, 'show'])->name('roles.show');
        Route::put('/{id}',    [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::prefix('permissions')->group(function () {
        Route::get('/',        [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/',       [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/{id}',    [PermissionController::class, 'show'])->name('permissions.show');
        Route::put('/{id}',    [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });
});
