@extends('admin.layouts.app')

@section('title', __('ui.settings'))
@section('page-title', __('ui.settings'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.settings') }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card table-card">
                <div class="card-header">
                    <h5>{{ __('ui.settings') }}</h5>
                </div>
                <div class="card-body pt-3">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.key') }}</th>
                                    <th>{{ __('ui.value') }}</th>
                                    <th>{{ __('ui.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($settings as $setting)
                                    <tr>
                                        <td>{{ $setting->key }}</td>
                                        <td>{{ is_array($setting->value) ? json_encode($setting->value) : $setting->value }}</td>
                                        <td>
                                            @can('update', \Modules\Settings\Models\Setting::class)
                                                <a href="{{ route('settings.show', $setting->key) }}" class="btn btn-sm btn-primary">{{ __('ui.edit') }}</a>
                                            @endcan
                                            @can('delete', \Modules\Settings\Models\Setting::class)
                                                <form method="POST" action="{{ route('settings.destroy', $setting->key) }}" style="display:inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center">{{ __('ui.no_records') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
