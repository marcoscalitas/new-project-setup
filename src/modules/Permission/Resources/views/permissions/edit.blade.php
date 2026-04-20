@extends('layouts.app')

@section('title', 'Edit ' . $permission->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('permissions.index') }}" class="text-sm hover:underline underline-offset-4">&larr; Back to permissions</a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-medium mb-6">Edit {{ $permission->name }}</h1>

    <form method="POST" action="{{ route('permissions.update', $permission->id) }}" class="flex flex-col gap-4">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium mb-1">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $permission->name) }}" required
                class="w-full px-3 py-2 rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-3 mt-2">
            <button type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
                Update
            </button>
            <a href="{{ route('permissions.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm text-sm font-medium hover:bg-[#f5f5f4] dark:hover:bg-[#1C1C1A] transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
