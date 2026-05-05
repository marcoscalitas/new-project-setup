<?php

use Illuminate\Support\Facades\Route;
use Modules\AuditLog\Http\Controllers\Api\AuditLogController;

Route::middleware(['auth:api', 'verified', 'throttle:60,1'])->prefix('audit-logs')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('api.audit-logs.index');
    Route::get('/users/{userUlid}', [AuditLogController::class, 'forUser'])->name('api.audit-logs.for-user');
    Route::get('/{id}', [AuditLogController::class, 'show'])->name('api.audit-logs.show');
});
