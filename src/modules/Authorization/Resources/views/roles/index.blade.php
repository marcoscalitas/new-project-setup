@extends('admin.layouts.app')

@section('title', __('ui.roles'))
@section('page-title', __('ui.roles'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.roles') }}</li>
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
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.roles_list') }}</h5>
                        <div>
                            @can('create', \Modules\Authorization\Models\Role::class)
                                <a href="{{ route('roles.create') }}" class="btn btn-primary">{{ __('ui.add_role') }}</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="px-4 pt-4 pb-4">
                        <x-admin::table-search
                            :action="route('roles.index')"
                            :value="request('search')"
                            :clear-url="request('search') ? route('roles.index', array_filter(['sort' => $sort !== 'name' ? $sort : null, 'direction' => $dir !== 'asc' ? $dir : null])) : null"
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
                                    <x-admin::sort-th column="name"       :label="__('ui.name')"  :currentSort="$sort" :currentDirection="$dir" />
                                    <x-admin::sort-th column="guard_name" :label="__('ui.guard')" :currentSort="$sort" :currentDirection="$dir" />
                                    <th>{{ __('ui.permissions') }}</th>
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
                                                    <div class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-secondary-500/10 text-secondary-500 font-semibold">
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
                                            <x-admin::badge color="primary" :label="__('ui.permission_count', ['count' => $role->permissions->count()])" />
                                        </td>
                                        <td>
                                            <x-admin::table-action-buttons
                                                :show-route="route('roles.show', $role->ulid)"
                                                :edit-route="route('roles.edit', $role->ulid)"
                                                :delete-route="route('roles.destroy', $role->ulid)"
                                                :model="$role"
                                                :confirm-message="__('ui.confirm_delete_role')"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin::empty-row :colspan="5" :message="__('ui.no_roles')" />
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
