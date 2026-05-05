@extends('admin.layouts.app')

@section('title', __('ui.trashed_roles'))
@section('page-title', __('ui.trashed_roles'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">{{ __('ui.roles') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.trashed') }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="sm:flex items-center justify-between">
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.trashed_roles') }}</h5>
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left mr-1"></i> {{ __('ui.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="flex items-center justify-between gap-4 overflow-x-auto px-4 pt-4 pb-4">
                        <x-admin::page-length :paginator="$roles" :action="route('roles.trashed')" />

                        <x-admin::table-search
                            :action="route('roles.trashed')"
                            :value="request('search')"
                            :clear-url="request('search') ? route('roles.trashed', array_filter(['per_page' => request('per_page')])) : null"
                        />
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('ui.name') }}</th>
                                    <th>{{ __('ui.guard') }}</th>
                                    <th>{{ __('ui.permissions') }}</th>
                                    <th>{{ __('ui.deleted_at') }}</th>
                                    <th>{{ __('ui.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $roles->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="shrink-0">
                                                    <div class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-danger-500/10 text-danger-500 font-semibold">
                                                        {{ strtoupper(substr($role->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="grow ltr:ml-3 rtl:mr-3">
                                                    <h6 class="mb-0">{{ $role->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $role->guard_name }}</td>
                                        <td>
                                            <x-admin::badge color="secondary" :label="__('ui.permission_count', ['count' => $role->permissions->count()])" />
                                        </td>
                                        <td>{{ $role->deleted_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <button type="button"
                                                class="w-8 h-8 rounded-xl inline-flex items-center justify-center btn-link-secondary btn-pc-default"
                                                data-pc-toggle="modal"
                                                data-pc-target="#restore-confirm-modal"
                                                data-restore-action="{{ route('roles.restore', $role->ulid) }}"
                                                data-restore-message="{{ __('ui.confirm_restore_role') }}"
                                                title="{{ __('ui.restore') }}">
                                                <i class="ti ti-rotate text-lg leading-none"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin::empty-row :colspan="6" :message="__('ui.no_trashed_roles')" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 pb-4">
                        <x-admin::pagination :paginator="$roles" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
