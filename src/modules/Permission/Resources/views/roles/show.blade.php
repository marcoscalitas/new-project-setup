@extends('layouts.app')

@section('title', $role->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('roles.index') }}" class="text-sm hover:underline underline-offset-4">&larr; Back to roles</a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-medium mb-6">{{ $role->name }}</h1>

    <dl class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Guard</dt>
            <dd>{{ $role->guard_name }}</dd>
        </div>
        <div class="py-3">
            <dt class="font-medium mb-2">Permissions</dt>
            <dd class="flex flex-wrap gap-2">
                @forelse($role->permissions as $permission)
                    <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                        {{ $permission->name }}
                    </span>
                @empty
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">No permissions</span>
                @endforelse
            </dd>
        </div>
    </dl>

    <div class="mt-6 flex gap-3">
        @can('update', $role)
            <a href="{{ route('roles.edit', $role->id) }}"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
                Edit
            </a>
        @endcan
        @can('delete', $role)
            <form method="POST" action="{{ route('roles.destroy', $role->id) }}">
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
