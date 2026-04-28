@extends('admin.layouts.app')

@section('title', $user->name)
@section('page-title', $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('ui.users') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $user->name }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12 md:col-span-8">
            <div class="card">
                <div class="card-header">
                    <div class="sm:flex items-center justify-between">
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.user_details') }}</h5>
                        <div>
                            @can('update', $user)
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-secondary mr-1">
                                    <i class="ti ti-edit mr-1"></i> {{ __('ui.edit') }}
                                </a>
                            @endcan
                            <x-admin::delete-form
                                :model="$user"
                                :delete-route="route('users.destroy', $user->id)"
                                :confirm-message="__('ui.confirm_delete_user')"
                            />
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-12 gap-x-6">
                        <x-admin::readonly-field :label="__('ui.name')"  :value="$user->name" />
                        <x-admin::readonly-field :label="__('ui.email')" :value="$user->email" />
                        <div class="col-span-12 md:col-span-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('ui.roles') }}</label>
                                <div class="mt-1">
                                    @forelse($user->roles as $role)
                                        <x-admin::badge color="primary" :label="$role->name" class="mr-1" />
                                    @empty
                                        <span class="text-muted">{{ __('ui.no_roles_assigned') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('ui.email_verified') }}</label>
                                <p class="mb-0">
                                    @if ($user->email_verified_at)
                                        <x-admin::badge color="success" :label="__('ui.verified')" />
                                    @else
                                        <x-admin::badge color="danger" :label="__('ui.not_verified')" />
                                    @endif
                                </p>
                            </div>
                        </div>
                        <x-admin::readonly-field :label="__('ui.created_at')" :value="$user->created_at->format('d/m/Y H:i')" />
                        <x-admin::readonly-field :label="__('ui.updated_at')" :value="$user->updated_at->format('d/m/Y H:i')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="w-20 h-20 rounded-full inline-flex items-center justify-center bg-primary-500/10 text-primary-500 text-3xl font-bold mx-auto mb-3">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-0">{{ $user->email }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
