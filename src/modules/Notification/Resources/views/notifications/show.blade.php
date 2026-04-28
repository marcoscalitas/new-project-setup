@extends('admin.layouts.app')

@section('title', __('ui.notification'))
@section('page-title', __('ui.notification'))

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
  <li class="breadcrumb-item"><a href="{{ route('notifications.index') }}">{{ __('ui.notifications') }}</a></li>
  <li class="breadcrumb-item" aria-current="page">{{ __('ui.detail') }}</li>
@endsection

@section('content')
<div class="grid grid-cols-12 gap-x-6">
  <div class="col-span-12 md:col-span-8">
    <div class="card">
      <div class="card-header">
        <div class="sm:flex items-center justify-between">
          <h5 class="mb-3 mb-sm-0">{{ __('ui.notification_details') }}</h5>
          <div>
            @if(!$notification->read_at)
              <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline-secondary mr-1">
                  <i class="ti ti-check mr-1"></i> {{ __('ui.mark_as_read') }}
                </button>
              </form>
            @endif
            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="inline"
              onsubmit="return confirm('{{ __('ui.confirm_delete_notification') }}')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger">
                <i class="ti ti-trash mr-1"></i> {{ __('ui.delete') }}
              </button>
            </form>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-12 gap-x-6">
          <x-admin::readonly-field :label="__('ui.type')" :value="$notification->data['type'] ?? '-'" />
          <div class="col-span-12 md:col-span-6">
            <div class="mb-3">
              <label class="form-label text-muted">{{ __('ui.status') }}</label>
              <p class="mb-0">
                @if($notification->read_at)
                  <x-admin::badge color="secondary" :label="__('ui.read')" />
                @else
                  <x-admin::badge color="primary" :label="__('ui.unread')" />
                @endif
              </p>
            </div>
          </div>
          <x-admin::readonly-field :label="__('ui.message')" :value="$notification->data['message'] ?? '-'" :span="12" />
          @if(!empty($notification->data['metadata']))
            <div class="col-span-12">
              <div class="mb-3">
                <label class="form-label text-muted">{{ __('ui.metadata') }}</label>
                <pre class="form-control text-sm" style="min-height: auto;">{{ json_encode($notification->data['metadata'], JSON_PRETTY_PRINT) }}</pre>
              </div>
            </div>
          @endif
          <x-admin::readonly-field :label="__('ui.received_at')" :value="$notification->created_at->format('d/m/Y H:i')" />
          @if($notification->read_at)
            <x-admin::readonly-field :label="__('ui.read_at')" :value="$notification->read_at->format('d/m/Y H:i')" />
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="col-span-12 md:col-span-4">
    <div class="card">
      <div class="card-body text-center">
        <div class="w-20 h-20 rounded-full inline-flex items-center justify-center {{ $notification->read_at ? 'bg-secondary-500/10 text-secondary-500' : 'bg-primary-500/10 text-primary-500' }} text-3xl font-bold mx-auto mb-3">
          <i class="ti ti-bell text-3xl leading-none"></i>
        </div>
        <h5 class="mb-1">{{ __('ui.notification') }}</h5>
        <p class="text-muted mb-0">{{ $notification->created_at->diffForHumans() }}</p>
      </div>
    </div>
  </div>
</div>
@endsection
