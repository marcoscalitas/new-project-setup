@extends('admin.layouts.app')

@section('title', __('ui.permissions'))
@section('page-title', __('ui.permissions'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.permissions') }}</li>
@endsection

@php
    $sort = request('sort', 'name');
    $dir  = request('direction', 'asc');
@endphp

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="sm:flex items-center justify-between">
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.permissions_list') }}</h5>
                        <div>
                            @can('create', \Modules\Authorization\Models\Permission::class)
                                <a href="{{ route('permissions.create') }}" class="btn btn-primary">{{ __('ui.add_permission') }}</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="flex items-center justify-between gap-4 overflow-x-auto px-4 pt-4 pb-4">
                        <x-admin::page-length :paginator="$permissions" :action="route('permissions.index')" />

                        <x-admin::table-search
                            :action="route('permissions.index')"
                            :value="request('search')"
                            :clear-url="request('search') ? route('permissions.index', array_filter(['sort' => $sort !== 'name' ? $sort : null, 'direction' => $dir !== 'asc' ? $dir : null])) : null"
                        >
                            @if($sort !== 'name') <input type="hidden" name="sort" value="{{ $sort }}"> @endif
                            @if($dir  !== 'asc')  <input type="hidden" name="direction" value="{{ $dir }}"> @endif
                        </x-admin::table-search>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <x-admin::sort-th column="name"       :label="__('ui.name')"       :currentSort="$sort" :currentDirection="$dir" />
                                    <x-admin::sort-th column="guard_name" :label="__('ui.guard')"      :currentSort="$sort" :currentDirection="$dir" />
                                    <x-admin::sort-th column="created_at" :label="__('ui.created_at')" :currentSort="$sort" :currentDirection="$dir" />
                                    <th>{{ __('ui.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                    <tr>
                                        <td>{{ $permissions->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="shrink-0">
                                                    <div class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-warning-500/10 text-warning-500 font-semibold">
                                                        <i class="ti ti-lock text-lg leading-none"></i>
                                                    </div>
                                                </div>
                                                <div class="grow ltr:ml-3 rtl:mr-3">
                                                    <h6 class="mb-0">{{ $permission->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $permission->guard_name }}</td>
                                        <td>{{ $permission->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <x-admin::table-action-buttons
                                                :show-route="route('permissions.show', $permission->ulid)"
                                                :edit-route="route('permissions.edit', $permission->ulid)"
                                                :delete-route="route('permissions.destroy', $permission->ulid)"
                                                :model="$permission"
                                                :confirm-message="__('ui.confirm_delete_permission')"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin::empty-row :colspan="5" :message="__('ui.no_permissions')" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 pb-4">
                        <x-admin::pagination :paginator="$permissions" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
