@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold mb-1">Welcome, {{ auth()->user()->name }}</h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ auth()->user()->email }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('users.index') }}"
            class="block p-5 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] hover:border-[#1b1b18] dark:hover:border-[#EDEDEC] transition-colors">
            <div class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide mb-1">Users</div>
            <div class="text-sm">Manage users and accounts</div>
        </a>

        <a href="{{ route('roles.index') }}"
            class="block p-5 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] hover:border-[#1b1b18] dark:hover:border-[#EDEDEC] transition-colors">
            <div class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide mb-1">Roles</div>
            <div class="text-sm">Manage roles and assignments</div>
        </a>

        <a href="{{ route('permissions.index') }}"
            class="block p-5 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] hover:border-[#1b1b18] dark:hover:border-[#EDEDEC] transition-colors">
            <div class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide mb-1">Permissions</div>
            <div class="text-sm">Manage access permissions</div>
        </a>

        <a href="{{ route('notifications.index') }}"
            class="block p-5 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] hover:border-[#1b1b18] dark:hover:border-[#EDEDEC] transition-colors">
            <div class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wide mb-1">Notifications</div>
            <div class="text-sm">View your notifications</div>
        </a>
    </div>
@endsection
