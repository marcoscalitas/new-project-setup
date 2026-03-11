<?php

use Illuminate\Support\Facades\Route;
use Modules\Web\Http\Controllers\WebController;

Route::prefix('web')
    ->middleware(['web', 'auth'])
    ->group(function () {
        // Web routes
    });
