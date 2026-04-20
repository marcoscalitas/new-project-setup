<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\RoleController;
use Modules\Permission\Http\Controllers\PermissionController;

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::prefix('roles')->group(function () {
        Route::get('/',           [RoleController::class, 'index'])->name('roles.index');
        Route::get('/create',     [RoleController::class, 'create'])->name('roles.create');
        Route::post('/',          [RoleController::class, 'store'])->name('roles.store');
        Route::get('/{id}',       [RoleController::class, 'show'])->name('roles.show');
        Route::get('/{id}/edit',  [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/{id}',       [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{id}',    [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::prefix('permissions')->group(function () {
        Route::get('/',           [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/create',     [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/',          [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/{id}',       [PermissionController::class, 'show'])->name('permissions.show');
        Route::get('/{id}/edit',  [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/{id}',       [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/{id}',    [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });
});
