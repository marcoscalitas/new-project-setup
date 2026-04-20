<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC]">
    <nav class="flex items-center justify-between px-6 py-4 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
        <a href="/" class="text-lg font-medium">{{ config('app.name', 'Laravel') }}</a>

        <div class="flex items-center gap-4 text-sm">
            @auth
                <a href="{{ route('users.index') }}" class="hover:underline underline-offset-4">Users</a>
                <a href="{{ route('roles.index') }}" class="hover:underline underline-offset-4">Roles</a>
                <a href="{{ route('permissions.index') }}" class="hover:underline underline-offset-4">Permissions</a>
                <a href="{{ route('notifications.index') }}" class="hover:underline underline-offset-4">
                    Notifications
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs bg-red-500 text-white rounded-full">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:underline underline-offset-4">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:underline underline-offset-4">Login</a>
                <a href="{{ route('register') }}" class="hover:underline underline-offset-4">Register</a>
            @endauth
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 rounded-sm bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 rounded-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 rounded-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
