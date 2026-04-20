@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="mb-6">
    <a href="{{ route('permissions.index') }}" class="text-sm hover:underline underline-offset-4">&larr; Back to permissions</a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-medium mb-6">Create Permission</h1>

    <form method="POST" action="{{ route('permissions.store') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium mb-1">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. post.create"
                class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-3 mt-2">
            <button type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
                Create
            </button>
            <a href="{{ route('permissions.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm text-sm font-medium hover:bg-[#f5f5f4] dark:hover:bg-[#1C1C1A] transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
