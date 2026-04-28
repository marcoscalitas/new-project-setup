@extends('admin.layouts.app')

@section('title', __('ui.notifications'))
@section('page-title', __('ui.notifications'))

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
  <li class="breadcrumb-item" aria-current="page">{{ __('ui.notifications') }}</li>
@endsection

@section('content')
<div class="grid grid-cols-12 gap-x-6">
  <div class="col-span-12">
    <div class="card table-card">
      <div class="card-header">
        <div class="sm:flex items-center justify-between">
          <h5 class="mb-3 mb-sm-0">{{ __('ui.notifications_list') }}</h5>
          <div>
            @if($notifications->where('read_at', null)->count() > 0)
              <form method="POST" action="{{ route('notifications.readAll') }}" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                  <i class="ti ti-checks mr-1"></i> {{ __('ui.mark_all_read') }}
                </button>
              </form>
            @endif
          </div>
        </div>
      </div>
      <div class="card-body pt-3">

        <div class="table-responsive">
          <table class="table table-hover" id="pc-dt-simple">
            <thead>
              <tr>
                <th>#</th>
                <th>{{ __('ui.message') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.received_at') }}</th>
                <th>{{ __('ui.action') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($notifications as $notification)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <div class="flex items-center">
                      <div class="shrink-0">
                        <div class="w-10 h-10 rounded-full inline-flex items-center justify-center {{ $notification->read_at ? 'bg-secondary-500/10 text-secondary-500' : 'bg-primary-500/10 text-primary-500' }}">
                          <i class="ti ti-bell text-lg leading-none"></i>
                        </div>
                      </div>
                      <div class="grow ltr:ml-3 rtl:mr-3">
                        <h6 class="mb-0">{{ $notification->data['message'] ?? 'Notification' }}</h6>
                        @if(!empty($notification->data['type']))
                          <small class="text-muted">{{ $notification->data['type'] }}</small>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($notification->read_at)
                      <x-admin::badge color="secondary" :label="__('ui.read')" />
                    @else
                      <x-admin::badge color="primary" :label="__('ui.unread')" />
                    @endif
                  </td>
                  <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                  <td>
                    <a href="{{ route('notifications.show', $notification->id) }}" class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary">
                      <i class="ti ti-eye text-xl leading-none"></i>
                    </a>
                    @if(!$notification->read_at)
                      <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary" title="Mark as read">
                          <i class="ti ti-check text-xl leading-none"></i>
                        </button>
                      </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="inline"
                      onsubmit="return confirm('{{ __('ui.confirm_delete_notification') }}')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary">
                        <i class="ti ti-trash text-xl leading-none"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <x-admin::empty-row :colspan="5" :message="__('ui.no_notifications')" />
              @endforelse
            </tbody>
          </table>
        </div>

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
