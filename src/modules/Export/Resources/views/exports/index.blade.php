@extends('admin.layouts.app')

@section('title', __('ui.exports'))
@section('page-title', __('ui.exports'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.exports') }}</li>
@endsection

@section('content')
<div class="grid grid-cols-12 gap-x-6">

    {{-- Request export form --}}
    <div class="col-span-12 lg:col-span-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('ui.request_export') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('exports.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">{{ __('ui.module') }}</label>
                        <select name="module" class="form-control @error('module') is-invalid @enderror">
                            <option value="users">{{ __('ui.users') }}</option>
                            <option value="activity_log">{{ __('ui.activity_log') }}</option>
                        </select>
                        @error('module')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">{{ __('ui.format') }}</label>
                        <select name="format" class="form-control @error('format') is-invalid @enderror">
                            <option value="xlsx">XLSX</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                        @error('format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="ti ti-download me-1"></i>{{ __('ui.request_export') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Exports list --}}
    <div class="col-span-12 lg:col-span-8">
        <div class="card table-card">
            <div class="card-header">
                <h5>{{ __('ui.my_exports') }}</h5>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('ui.module') }}</th>
                                <th>{{ __('ui.format') }}</th>
                                <th>{{ __('ui.status') }}</th>
                                <th>{{ __('ui.date') }}</th>
                                <th>{{ __('ui.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exports as $export)
                                <tr>
                                    <td>{{ __('ui.' . $export->module) }}</td>
                                    <td><x-admin::badge color="secondary" :label="strtoupper($export->format)" /></td>
                                    <td>
                                        @if($export->status === 'completed')
                                            <x-admin::badge color="success" :label="__('ui.completed')" />
                                        @elseif($export->status === 'failed')
                                            <x-admin::badge color="danger" :label="__('ui.failed')" />
                                        @else
                                            <x-admin::badge color="warning" :label="__('ui.pending')" />
                                        @endif
                                    </td>
                                    <td>{{ $export->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($export->isCompleted() && !$export->isExpired())
                                            <a href="{{ route('exports.download', $export->uuid) }}"
                                                class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary"
                                                title="{{ __('ui.download') }}">
                                                <i class="ti ti-download text-xl leading-none"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-admin::empty-row :colspan="5" :message="__('ui.no_exports')" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($exports->hasPages())
                    <div class="mt-4">
                        {{ $exports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
