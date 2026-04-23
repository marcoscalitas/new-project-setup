@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
        <h1 class="text-2xl font-medium mb-2">Verify Your Email</h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-6">
            Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent to you.
            If you didn't receive the email, we'll gladly send another.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 text-sm text-green-600 dark:text-green-400">
                A new verification link has been sent to your email address.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="flex flex-col gap-4">
            @csrf

            <button type="submit"
                class="w-full py-2 px-4 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm font-medium hover:opacity-90 transition-opacity">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full text-sm text-center hover:underline underline-offset-4">
                Log Out
            </button>
        </form>
@endsection
