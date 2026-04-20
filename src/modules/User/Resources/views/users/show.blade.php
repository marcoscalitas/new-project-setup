@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('users.index') }}" class="text-sm hover:underline underline-offset-4">&larr; Back to users</a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-medium mb-6">{{ $user->name }}</h1>

    <dl class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Email</dt>
            <dd>{{ $user->email }}</dd>
        </div>
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Roles</dt>
            <dd>
                @forelse($user->roles as $role)
                    <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                        {{ $role->name }}
                    </span>
                @empty
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">No roles</span>
                @endforelse
            </dd>
        </div>
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Created at</dt>
            <dd>{{ $user->created_at->format('d/m/Y H:i') }}</dd>
        </div>
    </dl>

    <div class="mt-6 flex gap-3">
        @can('update', $user)
            <a href="{{ route('users.edit', $user->id) }}"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
                Edit
            </a>
        @endcan
        @can('delete', $user)
            <form method="POST" action="{{ route('users.destroy', $user->id) }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 rounded-sm text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    onclick="return confirm('Are you sure?')">
                    Delete
                </button>
            </form>
        @endcan
    </div>
</div>
@endsection
