@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-medium">Notifications</h1>
    @if($notifications->where('read_at', null)->count() > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm text-sm font-medium hover:bg-[#f5f5f4] dark:hover:bg-[#1C1C1A] transition-colors">
                Mark all as read
            </button>
        </form>
    @endif
</div>

<div class="flex flex-col gap-3">
    @forelse($notifications as $notification)
        <div class="flex items-center justify-between p-4 rounded-sm border {{ $notification->read_at ? 'border-[#e3e3e0] dark:border-[#3E3E3A]' : 'border-blue-200 dark:border-blue-800 bg-blue-50/50 dark:bg-blue-900/10' }}">
            <div class="flex-1">
                <a href="{{ route('notifications.show', $notification->id) }}" class="hover:underline underline-offset-4">
                    <p class="text-sm {{ $notification->read_at ? 'text-[#706f6c] dark:text-[#A1A09A]' : 'font-medium' }}">
                        {{ $notification->data['message'] ?? 'Notification' }}
                    </p>
                </a>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                    {{ $notification->created_at->diffForHumans() }}
                </p>
            </div>
            <div class="flex items-center gap-2 ml-4">
                @if(!$notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline underline-offset-4">
                            Mark read
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline underline-offset-4"
                        onclick="return confirm('Are you sure?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-[#706f6c] dark:text-[#A1A09A] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm">
            No notifications.
        </div>
    @endforelse
</div>
@endsection
