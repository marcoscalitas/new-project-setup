<?php

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Services\AuthService;
use Modules\User\Models\User;

// Web auth routes are handled by Fortify (prefix 'auth' set in config/fortify.php)

// Strict verification notice: no unverified account should keep a web session.
Route::get('/auth/email/verify', function (Request $request) {
    $email = $request->user()?->email;

    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->withErrors(['activation' => __('auth.email_activation_sent')])
        ->withInput(['email' => $email]);
})->middleware('auth:web')->name('verification.notice');

// Public email verification route — does not require authentication.
// After verifying, the user is automatically logged in via web session.
Route::get('/auth/email/activate/{id}/{hash}', function (string $id, string $hash) {
    $user = User::findOrFail($id);

    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, __('auth.invalid_verification_link'));
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    Auth::guard('web')->login($user);

    return redirect('/')->with('status', 'email-verified');
})->middleware(['signed', 'throttle:6,1'])->name('verification.activate');

// Public resend verification page — no authentication required.
Route::get('/auth/email/resend', fn () => view('auth::resend-verification'))
    ->name('web.auth.email.resend');

Route::post('/auth/email/resend', function (Request $request, AuthService $authService) {
    $request->validate(['email' => ['required', 'email']]);
    $authService->resendVerificationEmail($request->input('email'));
    return redirect()->route('login')->with('status', 'verification-link-sent');
})->middleware('throttle:3,1')->name('web.auth.email.resend.send');
