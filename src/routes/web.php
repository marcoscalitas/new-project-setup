<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'pt'])) {
        session(['locale' => $locale]);
        cookie()->queue(cookie()->forever('locale', $locale));
    }
    return redirect()->back();
})->middleware(['web'])->name('locale.switch');

Route::get('/', function () {
    return view('admin.home', [
        'stats' => [
            'users'         => \Modules\User\Models\User::count(),
            'roles'         => \Spatie\Permission\Models\Role::count(),
            'permissions'   => \Spatie\Permission\Models\Permission::count(),
            'activity_logs' => \Spatie\Activitylog\Models\Activity::count(),
        ],
    ]);
})->middleware(['auth:web', 'verified'])->name('home');
