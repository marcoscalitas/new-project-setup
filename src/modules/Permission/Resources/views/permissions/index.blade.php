@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-medium">Permissions</h1>
    @can('create', \Modules\Permission\Models\Permission::class)
        <a href="{{ route('permissions.create') }}"
            class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
            New Permission
        </a>
    @endcan
</div>

<div class="overflow-hidden rounded-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
    <table class="w-full text-sm">
        <thead class="bg-[#f5f5f4] dark:bg-[#1C1C1A]">
            <tr>
                <th class="text-left px-4 py-3 font-medium">Name</th>
                <th class="text-left px-4 py-3 font-medium">Guard</th>
                <th class="text-right px-4 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            @forelse($permissions as $permission)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $permission->name }}</td>
                    <td class="px-4 py-3">{{ $permission->guard_name }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('permissions.show', $permission->id) }}" class="hover:underline underline-offset-4">View</a>
                        @can('update', $permission)
                            <a href="{{ route('permissions.edit', $permission->id) }}" class="hover:underline underline-offset-4">Edit</a>
                        @endcan
                        @can('delete', $permission)
                            <form method="POST" action="{{ route('permissions.destroy', $permission->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline underline-offset-4"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-[#706f6c] dark:text-[#A1A09A]">No permissions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
