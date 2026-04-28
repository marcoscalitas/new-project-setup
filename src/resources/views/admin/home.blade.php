@extends('admin.layouts.app')

@section('title', __('ui.dashboard'))
@section('page-title', __('ui.dashboard'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.dashboard') }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">

        <div class="col-span-12 lg:col-span-3 md:col-span-6">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div
                                class="w-12 h-12 rounded-lg inline-flex items-center justify-center bg-primary-500/10 text-primary-500">
                                <i class="ti ti-users text-2xl leading-none"></i>
                            </div>
                        </div>
                        <div class="grow ltr:ml-3 rtl:mr-3">
                            <p class="mb-1">{{ __('ui.total_users') }}</p>
                            <div class="flex items-center justify-between">
                                <h4 class="mb-0">{{ $stats['users'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-3 md:col-span-6">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div
                                class="w-12 h-12 rounded-lg inline-flex items-center justify-center bg-warning-500/10 text-warning-500">
                                <i class="ti ti-shield text-2xl leading-none"></i>
                            </div>
                        </div>
                        <div class="grow ltr:ml-3 rtl:mr-3">
                            <p class="mb-1">{{ __('ui.total_roles') }}</p>
                            <div class="flex items-center justify-between">
                                <h4 class="mb-0">{{ $stats['roles'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-3 md:col-span-6">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div
                                class="w-12 h-12 rounded-lg inline-flex items-center justify-center bg-success-500/10 text-success-500">
                                <i class="ti ti-lock text-2xl leading-none"></i>
                            </div>
                        </div>
                        <div class="grow ltr:ml-3 rtl:mr-3">
                            <p class="mb-1">{{ __('ui.permissions') }}</p>
                            <div class="flex items-center justify-between">
                                <h4 class="mb-0">{{ $stats['permissions'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-3 md:col-span-6">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div
                                class="w-12 h-12 rounded-lg inline-flex items-center justify-center bg-danger-500/10 text-danger-500">
                                <i class="ti ti-bell text-2xl leading-none"></i>
                            </div>
                        </div>
                        <div class="grow ltr:ml-3 rtl:mr-3">
                            <p class="mb-1">{{ __('ui.unread_notifications') }}</p>
                            <div class="flex items-center justify-between">
                                <h4 class="mb-0">{{ auth()->user()->unreadNotifications->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @can('viewAny', \Spatie\Activitylog\Models\Activity::class)
            <div class="col-span-12 lg:col-span-3 md:col-span-6">
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <div
                                    class="w-12 h-12 rounded-lg inline-flex items-center justify-center bg-secondary-500/10 text-secondary-500">
                                    <i class="ti ti-activity text-2xl leading-none"></i>
                                </div>
                            </div>
                            <div class="grow ltr:ml-3 rtl:mr-3">
                                <p class="mb-1">{{ __('ui.total_activity_logs') }}</p>
                                <div class="flex items-center justify-between">
                                    <h4 class="mb-0">{{ $stats['activity_logs'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

    </div>
@endsection
