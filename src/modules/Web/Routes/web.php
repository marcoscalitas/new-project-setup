<?php

use Illuminate\Support\Facades\Route;
use Modules\Web\Http\Controllers\WebController;

Route::prefix('web')
    ->middleware(['auth'])
    ->group(function () {
        // Web routes
    });
