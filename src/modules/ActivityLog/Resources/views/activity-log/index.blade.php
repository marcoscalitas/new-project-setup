@extends('admin.layouts.app')

@section('title', __('ui.activity_log'))
@section('page-title', __('ui.activity_log'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.activity_log') }}</li>
@endsection

@section('content')
<div class="grid grid-cols-12 gap-x-6">
    <div class="col-span-12">
        <div class="card table-card">
            <div class="card-header">
                <div class="sm:flex items-center justify-between">
                    <h5 class="mb-3 mb-sm-0">{{ __('ui.activity_log') }}</h5>
                    <form method="GET" class="flex items-center gap-2 flex-wrap">
                        <input type="text" name="log_name" value="{{ $filters['log_name'] ?? '' }}"
                            placeholder="{{ __('ui.log_name') }}"
                            class="form-control w-auto text-sm" />
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                            class="form-control w-auto text-sm" />
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                            class="form-control w-auto text-sm" />
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('ui.filter') }}</button>
                        <a href="{{ route('activity-log.index') }}" class="btn btn-light btn-sm">{{ __('ui.reset') }}</a>
                    </form>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('ui.log_name') }}</th>
                                <th>{{ __('ui.description') }}</th>
                                <th>{{ __('ui.causer') }}</th>
                                <th>{{ __('ui.subject') }}</th>
                                <th>{{ __('ui.date') }}</th>
                                <th>{{ __('ui.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <x-admin::badge color="primary" :label="$log->log_name" />
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td>
                                        @if($log->causer)
                                            {{ $log->causer->name ?? $log->causer_id }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->subject_type)
                                            <span class="text-sm text-muted">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('activity-log.show', $log->id) }}"
                                            class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary">
                                            <i class="ti ti-eye text-xl leading-none"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <x-admin::empty-row :colspan="7" :message="__('ui.no_logs')" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="mt-4">
                        {{ $logs->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin/theme/plugins/simple-datatables.js') }}"></script>
<script type="module">
    import { DataTable } from '{{ asset("admin/theme/plugins/module.js") }}';
    new DataTable('#pc-dt-simple', {
        columns: [{ select: 0, sortable: false, searchable: false }]
    });
</script>
@endpush
