@extends('admin.layouts.app')

@section('title', __('ui.edit_role') . ' — ' . $role->name)
@section('page-title', __('ui.edit_role'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">{{ __('ui.roles') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.edit_role') }} {{ $role->name }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('ui.edit_role') }} — {{ $role->name }}</h5>
                </div>
                <div class="card-body">

                    <x-admin::form-errors />

                    <form method="POST" action="{{ route('roles.update', $role->ulid) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="col-span-12 md:col-span-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.role_name') }}</label>
                                    <input type="text" name="name" class="form-control" placeholder="{{ __('ui.enter_role_name') }}"
                                        value="{{ old('name', $role->name) }}" required />
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.permissions') }}</label>
                                    <input type="hidden" name="permissions" value="" />
                                    <div class="flex flex-wrap gap-4 mt-1">
                                        @foreach ($permissions as $permission)
                                            <div class="form-check">
                                                <input class="form-check-input input-primary" type="checkbox"
                                                    name="permissions[]" value="{{ $permission->name }}"
                                                    id="perm_{{ $permission->id }}"
                                                    {{ in_array($permission->name, (array) old('permissions', $role->permissions->pluck('name')->toArray())) ? 'checked' : '' }} />
                                                <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <x-admin::form-buttons :cancel-route="route('roles.index')" :submit-label="__('ui.update_role')" />
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
