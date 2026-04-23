<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');

Route::get('/', function () {
    return view('home');
})->middleware(['auth:web'])->name('home');
