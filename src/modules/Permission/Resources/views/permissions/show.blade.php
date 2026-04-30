@extends('admin.layouts.app')

@section('title', $permission->name)
@section('page-title', $permission->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">{{ __('ui.permissions') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $permission->name }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12 md:col-span-8">
            <div class="card">
                <div class="card-header">
                    <div class="sm:flex items-center justify-between">
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.permission_details') }}</h5>
                        <div>
                            @can('update', $permission)
                                <a href="{{ route('permissions.edit', $permission->ulid) }}" class="btn btn-outline-secondary mr-1">
                                    <i class="ti ti-edit mr-1"></i> {{ __('ui.edit') }}
                                </a>
                            @endcan
                            <x-admin::delete-form
                                :model="$permission"
                                :delete-route="route('permissions.destroy', $permission->ulid)"
                                :confirm-message="__('ui.confirm_delete_permission')"
                            />
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-12 gap-x-6">
                        <x-admin::readonly-field :label="__('ui.name')"       :value="$permission->name" />
                        <x-admin::readonly-field :label="__('ui.guard')"      :value="$permission->guard_name" />
                        <x-admin::readonly-field :label="__('ui.created_at')" :value="$permission->created_at->format('d/m/Y H:i')" />
                        <x-admin::readonly-field :label="__('ui.updated_at')" :value="$permission->updated_at->format('d/m/Y H:i')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="w-20 h-20 rounded-full inline-flex items-center justify-center bg-warning-500/10 text-warning-500 text-3xl font-bold mx-auto mb-3">
                        <i class="ti ti-lock text-3xl leading-none"></i>
                    </div>
                    <h5 class="mb-1">{{ $permission->name }}</h5>
                    <p class="text-muted mb-0">{{ $permission->guard_name }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
