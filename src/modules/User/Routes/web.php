<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::prefix('users')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/',        [UserController::class, 'index'])->name('users.index');
        Route::post('/',       [UserController::class, 'store'])->name('users.store');
        Route::get('/{id}',    [UserController::class, 'show'])->name('users.show');
        Route::put('/{id}',    [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
