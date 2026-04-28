@extends('auth::emails.layout')

@section('title', 'Welcome to ' . config('app.name'))

@section('content')
    <p>Hi {{ $user->name }},</p>
    <p>Welcome to <strong>{{ config('app.name') }}</strong>! We're glad to have you on board.</p>
    <p>Your account has been created successfully. You can now log in and start using the platform.</p>

    <div class="btn-wrapper">
        <a href="{{ config('app.url') }}" class="btn">Get Started</a>
    </div>

    <hr class="divider">

    <p class="footnote">If you did not create an account, no further action is required.</p>
@endsection
