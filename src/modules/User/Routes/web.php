<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Web\UserController;
use Modules\User\Http\Controllers\Web\ProfileController;

Route::prefix('users')
    ->middleware(['auth', 'verified', 'throttle:60,1'])
    ->group(function () {
        Route::get('/',           [UserController::class, 'index'])->name('users.index');
        Route::get('/create',     [UserController::class, 'create'])->name('users.create');
        Route::post('/',          [UserController::class, 'store'])->name('users.store');
        Route::get('/trashed',          [UserController::class, 'trashed'])->name('users.trashed');
        Route::patch('/{ulid}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::get('/{user}',           [UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit',  [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}',       [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}',    [UserController::class, 'destroy'])->name('users.destroy');
    });

Route::prefix('profile')
    ->middleware(['auth', 'verified', 'throttle:60,1'])
    ->group(function () {
        Route::get('/',                [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/',                [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password',        [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/avatar',         [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    });
