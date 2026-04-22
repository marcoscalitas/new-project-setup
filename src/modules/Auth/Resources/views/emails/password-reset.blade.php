@extends('emails.layout')

@section('title', 'Reset Your Password')

@section('content')
    <p>Hi {{ $user->name }},</p>
    <p>We received a request to reset the password for your account. Click the button below to proceed.</p>

    <div class="btn-wrapper">
        <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
    </div>

    <hr class="divider">

    <p class="footnote">This link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>
    <p class="footnote">If you did not request a password reset, no further action is required.</p>
    <p class="footnote">If the button above does not work, copy and paste the following URL into your browser:<br>{{ $resetUrl }}</p>
@endsection
