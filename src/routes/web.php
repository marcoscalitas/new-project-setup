<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;
use Modules\AuditLog\Models\AuditLog;
use Modules\User\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
            'audit_logs' => AuditLog::count(),
        ],
    ]);
})->middleware(['auth:web', 'verified'])->name('home');
