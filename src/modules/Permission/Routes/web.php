<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\RoleController;
use Modules\Permission\Http\Controllers\PermissionController;

Route::prefix('permissions')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/roles',            [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles',           [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}',       [RoleController::class, 'show'])->name('roles.show');
        Route::put('/roles/{id}',       [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}',    [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/permissions',            [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions',           [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/permissions/{id}',       [PermissionController::class, 'show'])->name('permissions.show');
        Route::put('/permissions/{id}',       [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{id}',    [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });
