@extends('admin.layouts.app')

@section('title', __('ui.edit_user') . ' — ' . $user->name)
@section('page-title', __('ui.edit_user'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('ui.users') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.edit_user') }} {{ $user->name }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('ui.edit_user') }} — {{ $user->name }}</h5>
                </div>
                <div class="card-body">

                    <x-admin::form-errors />

                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="col-span-12 md:col-span-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.name') }}</label>
                                    <input type="text" name="name" class="form-control" placeholder="{{ __('ui.enter_full_name') }}"
                                        value="{{ old('name', $user->name) }}" required />
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.email') }}</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="{{ __('ui.enter_email') }}" value="{{ old('email', $user->email) }}" required />
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.password') }} <span class="text-muted">({{ __('ui.password_hint') }})</span></label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="{{ __('ui.new_password_placeholder') }}" />
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.confirm_password') }}</label>
                                    <input type="password" name="password_confirmation" class="form-control"
                                        placeholder="{{ __('ui.confirm_new_password_placeholder') }}" />
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ui.roles') }}</label>
                                    <input type="hidden" name="roles" value="" />
                                    <div class="flex flex-wrap gap-4 mt-1">
                                        @foreach ($roles as $role)
                                            <div class="form-check">
                                                <input class="form-check-input input-primary" type="checkbox" name="roles[]"
                                                    value="{{ $role->name }}" id="role_{{ $role->id }}"
                                                    {{ in_array($role->name, (array) old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }} />
                                                <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <x-admin::form-buttons :cancel-route="route('users.index')" :submit-label="__('ui.update_user')" />
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
