@extends('layouts.app')

@section('title', $permission->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('permissions.index') }}" class="text-sm hover:underline underline-offset-4">&larr; Back to permissions</a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-medium mb-6">{{ $permission->name }}</h1>

    <dl class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Guard</dt>
            <dd>{{ $permission->guard_name }}</dd>
        </div>
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Created at</dt>
            <dd>{{ $permission->created_at->format('d/m/Y H:i') }}</dd>
        </div>
    </dl>

    <div class="mt-6 flex gap-3">
        @can('update', $permission)
            <a href="{{ route('permissions.edit', $permission->id) }}"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
                Edit
            </a>
        @endcan
        @can('delete', $permission)
            <form method="POST" action="{{ route('permissions.destroy', $permission->id) }}">
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
