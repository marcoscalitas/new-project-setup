@extends('admin.layouts.app')

@section('title', __('ui.edit_permission') . ' — ' . $permission->name)
@section('page-title', __('ui.edit_permission'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">{{ __('ui.permissions') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.edit_permission') }} {{ $permission->name }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('ui.edit_permission') }} — {{ $permission->name }}</h5>
                </div>
                <div class="card-body">

                    <x-admin::form-errors />

                    <form method="POST" action="{{ route('permissions.update', $permission->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="col-span-12 md:col-span-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.permission_name') }}</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. user.create"
                                        value="{{ old('name', $permission->name) }}" required />
                                </div>
                            </div>
                            <x-admin::form-buttons :cancel-route="route('permissions.index')" :submit-label="__('ui.update_permission')" />
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
