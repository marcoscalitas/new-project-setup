@extends('admin.layouts.app')

@section('title', __('ui.activity_log_detail'))
@section('page-title', __('ui.activity_log_detail'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('activity-log.index') }}">{{ __('ui.activity_log') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">#{{ $activity->id }}</li>
@endsection

@section('content')
<div class="grid grid-cols-12 gap-x-6">
    <div class="col-span-12 lg:col-span-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('ui.activity_log_detail') }}</h5>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-muted mb-1">{{ __('ui.log_name') }}</dt>
                        <dd>
                            <x-admin::badge color="primary" :label="$activity->log_name" />
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-muted mb-1">{{ __('ui.date') }}</dt>
                        <dd>{{ $activity->created_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="font-medium text-muted mb-1">{{ __('ui.description') }}</dt>
                        <dd>{{ $activity->description }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-muted mb-1">{{ __('ui.causer') }}</dt>
                        <dd>
                            @if($activity->causer)
                                {{ $activity->causer->name ?? '—' }}
                                <span class="text-muted text-xs">(#{{ $activity->causer_id }})</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-muted mb-1">{{ __('ui.subject') }}</dt>
                        <dd>
                            @if($activity->subject_type)
                                {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>
                    </div>
                    @if($activity->properties && $activity->properties->count())
                    <div class="col-span-2">
                        <dt class="font-medium text-muted mb-1">{{ __('ui.properties') }}</dt>
                        <dd>
                            <pre class="bg-gray-100 dark:bg-gray-800 rounded p-3 text-xs overflow-auto">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('activity-log.index') }}" class="btn btn-light">← {{ __('ui.back') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
