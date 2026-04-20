@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="mb-6">
    <a href="{{ route('users.index') }}" class="text-sm hover:underline underline-offset-4">&larr; Back to users</a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-medium mb-6">Create User</h1>

    <form method="POST" action="{{ route('users.store') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium mb-1">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input type="password" id="password" name="password" required
                class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Roles</label>
            <div class="flex flex-wrap gap-3">
                @foreach($roles as $role)
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                            {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                            class="rounded-sm">
                        <span class="text-sm">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 mt-2">
            <button type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
                Create
            </button>
            <a href="{{ route('users.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm text-sm font-medium hover:bg-[#f5f5f4] dark:hover:bg-[#1C1C1A] transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
