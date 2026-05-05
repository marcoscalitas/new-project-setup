@extends('admin.layouts.app')

@section('title', __('ui.notifications'))
@section('page-title', __('ui.notifications'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.notifications') }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card">
                <div class="card-header">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h5 class="mb-1">{{ __('ui.notifications') }}</h5>
                            <p class="mb-0 text-muted">{{ __('ui.unread') }}: {{ $unreadCount }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('notifications.index') }}"
                                class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            >
                                {{ __('ui.all') }}
                            </a>
                            <a
                                href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                                class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            >
                                {{ __('ui.unread') }}
                            </a>
                            @if ($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link">{{ __('ui.mark_all_read') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @php $previousNotificationGroup = null; @endphp

                    @forelse($notifications as $notification)
                        @php
                            $payload = $notification->data ?? [];
                            $type = data_get($payload, 'type', 'notification');
                            $message = data_get($payload, 'message', __('ui.new_notification'));
                            $parts = explode(':', $message, 2);
                            $title = trim($parts[0] ?? __('ui.new_notification'));
                            $body = trim($parts[1] ?? $message);
                            $group = $notification->created_at->isToday()
                                ? __('ui.today')
                                : ($notification->created_at->isYesterday()
                                    ? __('ui.yesterday')
                                    : __('ui.earlier'));
                            $icon = match ($type) {
                                'user_created' => 'custom-user-bold',
                                'role_created', 'role_updated', 'role_deleted' => 'custom-shield',
                                'permission_created', 'permission_updated', 'permission_deleted' => 'custom-lock-outline',
                                default => 'custom-notification',
                            };
                        @endphp

                        @if ($previousNotificationGroup !== $group)
                            <p class="text-span mb-3 {{ $previousNotificationGroup ? 'mt-4' : '' }}">{{ $group }}</p>
                            @php $previousNotificationGroup = $group; @endphp
                        @endif

                        <a
                            href="{{ route('notifications.redirect', $notification->id) }}"
                            class="card mb-2 block text-theme-body no-underline transition hover:text-theme-body hover:shadow-sm dark:text-themedark-body dark:hover:text-themedark-body {{ $notification->read_at ? 'bg-white dark:bg-themedark-cardbg' : 'border-primary-500 bg-primary-500/10' }}"
                        >
                            <div class="card-body">
                                <div class="flex gap-4">
                                    <div class="shrink-0">
                                        <svg class="pc-icon {{ $notification->read_at ? 'text-muted' : 'text-primary-500' }} w-[22px] h-[22px]">
                                            <use xlink:href="#{{ $icon }}"></use>
                                        </svg>
                                    </div>
                                    <div class="grow">
                                        <div class="mb-2 flex items-start justify-between gap-3">
                                            <h5 class="text-body mb-0 {{ $notification->read_at ? 'text-muted' : '' }}">{{ $title }}</h5>
                                            <span class="shrink-0 text-sm text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mb-2 {{ $notification->read_at ? 'text-muted' : '' }}">{{ $body }}</p>
                                        <span class="text-sm text-primary-500">{{ $group }}</span>
                                    </div>
                                    @unless($notification->read_at)
                                        <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-primary-500"></span>
                                    @endunless
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-center text-muted py-4 mb-0">{{ __('ui.no_notifications') }}</p>
                    @endforelse

                    <div class="mt-4">
                        <x-admin::pagination :paginator="$notifications" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
