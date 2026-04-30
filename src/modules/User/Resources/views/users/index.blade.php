@extends('admin.layouts.app')

@section('title', __('ui.users'))
@section('page-title', __('ui.users'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.users') }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="sm:flex items-center justify-between">
                        <h5 class="mb-3 mb-sm-0">{{ __('ui.user_list') }}</h5>
                        <div>
                            @can('create', \Modules\User\Models\User::class)
                                <a href="{{ route('users.create') }}" class="btn btn-primary">{{ __('ui.add_user') }}</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('ui.name') }}</th>
                                    <th>{{ __('ui.email') }}</th>
                                    <th>{{ __('ui.roles') }}</th>
                                    <th>{{ __('ui.created_at') }}</th>
                                    <th>{{ __('ui.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="shrink-0">
                                                    <div class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-primary-500/10 text-primary-500 font-semibold">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="grow ltr:ml-3 rtl:mr-3">
                                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @foreach ($user->roles as $role)
                                                <x-admin::badge color="primary" :label="$role->name" />
                                            @endforeach
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <x-admin::table-action-buttons
                                                :show-route="route('users.show', $user->ulid)"
                                                :edit-route="route('users.edit', $user->ulid)"
                                                :delete-route="route('users.destroy', $user->ulid)"
                                                :model="$user"
                                                :confirm-message="__('ui.confirm_delete_user')"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin::empty-row :colspan="6" :message="__('ui.no_users')" />
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
