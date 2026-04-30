@extends('admin.layouts.app')

@section('title', $role->name)
@section('page-title', $role->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">{{ __('ui.roles') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $role->name }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12 md:col-span-8">
            <div class="card">
                <div class="card-header">
                    <div class="sm:flex items-center justify-between">
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.role_details') }}</h5>
                        <div>
                            @can('update', $role)
                                <a href="{{ route('roles.edit', $role->ulid) }}" class="btn btn-outline-secondary mr-1">
                                    <i class="ti ti-edit mr-1"></i> {{ __('ui.edit') }}
                                </a>
                            @endcan
                            <x-admin::delete-form
                                :model="$role"
                                :delete-route="route('roles.destroy', $role->ulid)"
                                :confirm-message="__('ui.confirm_delete_role')"
                            />
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-12 gap-x-6">
                        <x-admin::readonly-field :label="__('ui.name')"  :value="$role->name" />
                        <x-admin::readonly-field :label="__('ui.guard')" :value="$role->guard_name" />
                        <div class="col-span-12">
                            <div class="mb-3">
                                <label class="form-label text-muted">{{ __('ui.permissions') }}</label>
                                <div class="mt-1">
                                    @forelse($role->permissions as $permission)
                                        <x-admin::badge color="primary" :label="$permission->name" class="mr-1 mb-1" />
                                    @empty
                                        <span class="text-muted">{{ __('ui.no_permissions_assigned') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <x-admin::readonly-field :label="__('ui.created_at')" :value="$role->created_at->format('d/m/Y H:i')" />
                        <x-admin::readonly-field :label="__('ui.updated_at')" :value="$role->updated_at->format('d/m/Y H:i')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="w-20 h-20 rounded-full inline-flex items-center justify-center bg-secondary-500/10 text-secondary-500 text-3xl font-bold mx-auto mb-3">
                        {{ strtoupper(substr($role->name, 0, 1)) }}
                    </div>
                    <h5 class="mb-1">{{ $role->name }}</h5>
                    <p class="text-muted mb-0">{{ __('ui.permission_count', ['count' => $role->permissions->count()]) }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
