@extends('layouts.app')

@section('title', 'Notification')

@section('content')
<div class="mb-6">
    <a href="{{ route('notifications.index') }}" class="text-sm hover:underline underline-offset-4">&larr; Back to notifications</a>
</div>

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <h1 class="text-2xl font-medium">Notification</h1>
        @if($notification->read_at)
            <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-[#706f6c] dark:text-[#A1A09A]">
                Read
            </span>
        @else
            <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                Unread
            </span>
        @endif
    </div>

    <dl class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Type</dt>
            <dd>{{ $notification->data['type'] ?? '-' }}</dd>
        </div>
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Message</dt>
            <dd>{{ $notification->data['message'] ?? '-' }}</dd>
        </div>
        @if(!empty($notification->data['metadata']))
            <div class="py-3">
                <dt class="font-medium mb-2">Metadata</dt>
                <dd class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    <pre class="bg-[#f5f5f4] dark:bg-[#1C1C1A] p-3 rounded-sm overflow-x-auto">{{ json_encode($notification->data['metadata'], JSON_PRETTY_PRINT) }}</pre>
                </dd>
            </div>
        @endif
        <div class="py-3 flex justify-between">
            <dt class="font-medium">Received at</dt>
            <dd>{{ $notification->created_at->format('d/m/Y H:i') }}</dd>
        </div>
        @if($notification->read_at)
            <div class="py-3 flex justify-between">
                <dt class="font-medium">Read at</dt>
                <dd>{{ $notification->read_at->format('d/m/Y H:i') }}</dd>
            </div>
        @endif
    </dl>

    <div class="mt-6 flex gap-3">
        @if(!$notification->read_at)
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm text-sm font-medium hover:opacity-90 transition-opacity">
                    Mark as read
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="px-4 py-2 border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 rounded-sm text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                onclick="return confirm('Are you sure?')">
                Delete
            </button>
        </form>
    </div>
</div>
@endsection
