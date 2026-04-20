@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-md">
        <h1 class="text-2xl font-medium mb-2">Forgot Password</h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-6">Enter your email and we'll send you a reset link.</p>

        @if(session('status'))
            <div class="mb-4 p-4 rounded-sm bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full py-2 px-4 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm font-medium hover:opacity-90 transition-opacity">
                Send Reset Link
            </button>

            <div class="text-sm text-center">
                <a href="{{ route('login') }}" class="hover:underline underline-offset-4">Back to login</a>
            </div>
        </form>
    </div>
</div>
@endsection
