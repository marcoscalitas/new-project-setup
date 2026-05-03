<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\Web\RoleController;
use Modules\Permission\Http\Controllers\Web\PermissionController;

Route::middleware(['auth', 'verified', 'throttle:60,1'])->group(function () {
    Route::prefix('roles')->group(function () {
        Route::get('/',              [RoleController::class, 'index'])->name('roles.index');
        Route::get('/create',        [RoleController::class, 'create'])->name('roles.create');
        Route::post('/',             [RoleController::class, 'store'])->name('roles.store');
        Route::get('/trashed',          [RoleController::class, 'trashed'])->name('roles.trashed');
        Route::patch('/{ulid}/restore', [RoleController::class, 'restore'])->name('roles.restore');
        Route::get('/{role}',           [RoleController::class, 'show'])->name('roles.show');
        Route::get('/{role}/edit',   [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/{role}',        [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}',     [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::prefix('permissions')->group(function () {
        Route::get('/',                   [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/create',             [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/',                  [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/trashed',            [PermissionController::class, 'trashed'])->name('permissions.trashed');
        Route::patch('/{ulid}/restore',   [PermissionController::class, 'restore'])->name('permissions.restore');
        Route::get('/{permission}',       [PermissionController::class, 'show'])->name('permissions.show');
        Route::get('/{permission}/edit',  [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/{permission}',       [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/{permission}',    [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });
});
