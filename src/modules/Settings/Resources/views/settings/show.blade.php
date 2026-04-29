@extends('admin.layouts.app')

@section('title', __('ui.edit_setting'))
@section('page-title', __('ui.edit_setting'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">{{ __('ui.settings') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $setting->key }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ $setting->key }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update', $setting->key) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">{{ __('ui.value') }}</label>
                            <input type="text" name="value" class="form-control" value="{{ is_array($setting->value) ? json_encode($setting->value) : $setting->value }}">
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('ui.save') }}</button>
                        <a href="{{ route('settings.index') }}" class="btn btn-secondary">{{ __('ui.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
