@extends('admin.layouts.app')

@section('title', __('ui.trashed_permissions'))
@section('page-title', __('ui.trashed_permissions'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">{{ __('ui.permissions') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.trashed') }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="sm:flex items-center justify-between">
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.trashed_permissions') }}</h5>
                        <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left mr-1"></i> {{ __('ui.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('ui.name') }}</th>
                                    <th>{{ __('ui.guard') }}</th>
                                    <th>{{ __('ui.deleted_at') }}</th>
                                    <th>{{ __('ui.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="shrink-0">
                                                    <div class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-danger-500/10 text-danger-500 font-semibold">
                                                        <i class="ti ti-lock text-lg leading-none"></i>
                                                    </div>
                                                </div>
                                                <div class="grow ltr:ml-3 rtl:mr-3">
                                                    <h6 class="mb-0">{{ $permission->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $permission->guard_name }}</td>
                                        <td>{{ $permission->deleted_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('permissions.restore', $permission->ulid) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary"
                                                    title="{{ __('ui.restore') }}">
                                                    <i class="ti ti-rotate text-xl leading-none"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin::empty-row :colspan="5" :message="__('ui.no_trashed_permissions')" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('admin.layouts.partials.datatable-scripts')
