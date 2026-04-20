@extends('layouts.guest')

@section('title', 'Login')

@section('content')
        <h1 class="text-2xl font-medium mb-6">Login</h1>

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" class="rounded-sm">
                <label for="remember" class="text-sm">Remember me</label>
            </div>

            <button type="submit"
                class="w-full py-2 px-4 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm font-medium hover:opacity-90 transition-opacity">
                Login
            </button>

            <div class="flex items-center justify-between text-sm">
                <a href="{{ route('password.request') }}" class="hover:underline underline-offset-4">Forgot password?</a>
                <a href="{{ route('register') }}" class="hover:underline underline-offset-4">Create account</a>
            </div>
        </form>
@endsection
