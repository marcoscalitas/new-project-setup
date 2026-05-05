@extends('admin.layouts.app')

@section('title', __('ui.my_profile'))
@section('page-title', __('ui.my_profile'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.my_profile') }}</li>
@endsection

@section('content')
@php
    $avatarUrl = $user->getAvatarUrl(90);
    $activeTab  = session('profile_tab', 'profile-1');
    // Keep active tab after redirect (redirect with tab info)
    if ($errors->has('avatar')) { $activeTab = 'profile-2'; }
    if ($errors->has('name') || $errors->has('email')) { $activeTab = 'profile-2'; }
    if ($errors->has('current_password') || $errors->has('password')) { $activeTab = 'profile-3'; }
@endphp

<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">

        {{-- Tab navigation --}}
        <div class="card">
            <div class="card-body !py-0">
                <ul class="flex flex-wrap w-full font-medium text-center nav-tabs" id="profileTabs">
                    <li class="group {{ $activeTab === 'profile-1' ? 'active' : '' }}">
                        <a href="javascript:void(0);" data-pc-toggle="tab" data-pc-target="profile-1"
                            class="inline-flex items-center mr-6 py-4 transition-all duration-300 ease-linear border-t-2 border-b-2 border-transparent group-[.active]:text-primary-500 group-[.active]:border-b-primary-500 hover:text-primary-500">
                            <i class="ti ti-user ltr:mr-2 rtl:ml-2 text-lg leading-none"></i>
                            {{ __('ui.profile_overview') }}
                        </a>
                    </li>
                    <li class="group {{ $activeTab === 'profile-2' ? 'active' : '' }}">
                        <a href="javascript:void(0);" data-pc-toggle="tab" data-pc-target="profile-2"
                            class="inline-flex items-center mr-6 py-4 transition-all duration-300 ease-linear border-t-2 border-b-2 border-transparent group-[.active]:text-primary-500 group-[.active]:border-b-primary-500 hover:text-primary-500">
                            <i class="ti ti-file-text ltr:mr-2 rtl:ml-2 text-lg leading-none"></i>
                            {{ __('ui.personal_details') }}
                        </a>
                    </li>
                    <li class="group {{ $activeTab === 'profile-3' ? 'active' : '' }}">
                        <a href="javascript:void(0);" data-pc-toggle="tab" data-pc-target="profile-3"
                            class="inline-flex items-center mr-6 py-4 transition-all duration-300 ease-linear border-t-2 border-b-2 border-transparent group-[.active]:text-primary-500 group-[.active]:border-b-primary-500 hover:text-primary-500">
                            <i class="ti ti-lock ltr:mr-2 rtl:ml-2 text-lg leading-none"></i>
                            {{ __('ui.change_password') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content">

            {{-- ── Tab 1: Profile Overview ── --}}
            <div class="{{ $activeTab === 'profile-1' ? 'block' : 'hidden' }} tab-pane" id="profile-1">
                <div class="grid grid-cols-12 gap-6">

                    {{-- Left: avatar card --}}
                    <div class="col-span-12 lg:col-span-4 2xl:col-span-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center pt-2">
                                    <div class="inline-flex mx-auto mb-3">
                                        <img class="rounded-full w-[90px] h-[90px] object-cover ring-4 ring-primary-500/20"
                                             src="{{ $avatarUrl }}" alt="{{ $user->name }}" />
                                    </div>
                                    <h5 class="mb-0">{{ $user->name }}</h5>
                                    <p class="text-muted text-sm mb-3">
                                        @forelse($user->roles as $role)
                                            <x-admin::badge color="primary" :label="$role->name" class="me-1" />
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </p>
                                    <hr class="my-4 border-secondary-500/10" />
                                    <div class="text-start *:flex *:items-center *:gap-3 *:mb-3 last:*:mb-0">
                                        <div>
                                            <i class="ti ti-mail text-primary-500"></i>
                                            <span class="text-sm">{{ $user->email }}</span>
                                        </div>
                                        <div>
                                            <i class="ti ti-calendar text-primary-500"></i>
                                            <span class="text-sm">{{ __('ui.member_since') }}: {{ $user->created_at->format('d M Y') }}</span>
                                        </div>
                                        <div>
                                            <i class="ti ti-shield-check text-primary-500"></i>
                                            <span class="text-sm">
                                                @if($user->email_verified_at)
                                                    <span class="text-success-500">{{ __('ui.verified') }}</span>
                                                @else
                                                    <span class="text-danger-500">{{ __('ui.not_verified') }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right: stats + roles --}}
                    <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
                        <div class="grid grid-cols-12 gap-6">

                            {{-- Stat cards --}}
                            <div class="col-span-12 sm:col-span-6 xl:col-span-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-xl inline-flex items-center justify-center bg-primary-500/10 text-primary-500 shrink-0">
                                                <i class="ti ti-bell text-xl leading-none"></i>
                                            </div>
                                            <div>
                                                <p class="text-muted text-sm mb-0">{{ __('ui.unread_notifications') }}</p>
                                                <h5 class="mb-0">{{ $user->unreadNotifications->count() }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-12 sm:col-span-6 xl:col-span-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-xl inline-flex items-center justify-center bg-success-500/10 text-success-500 shrink-0">
                                                <i class="ti ti-shield text-xl leading-none"></i>
                                            </div>
                                            <div>
                                                <p class="text-muted text-sm mb-0">{{ __('ui.roles_assigned') }}</p>
                                                <h5 class="mb-0">{{ $user->roles->count() }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-12 sm:col-span-6 xl:col-span-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-xl inline-flex items-center justify-center bg-warning-500/10 text-warning-500 shrink-0">
                                                <i class="ti ti-activity text-xl leading-none"></i>
                                            </div>
                                            <div>
                                                <p class="text-muted text-sm mb-0">{{ __('ui.audit_log') }}</p>
                                                <h5 class="mb-0">{{ \Modules\AuditLog\Models\AuditLog::where('causer_id', $user->id)->count() }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Account details card --}}
                            <div class="col-span-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('ui.account') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="grid grid-cols-12 gap-4">
                                            <div class="col-span-12 sm:col-span-6">
                                                <p class="text-muted text-sm mb-1">{{ __('ui.name') }}</p>
                                                <p class="mb-0 font-medium">{{ $user->name }}</p>
                                            </div>
                                            <div class="col-span-12 sm:col-span-6">
                                                <p class="text-muted text-sm mb-1">{{ __('ui.email') }}</p>
                                                <p class="mb-0 font-medium">{{ $user->email }}</p>
                                            </div>
                                            <div class="col-span-12 sm:col-span-6">
                                                <p class="text-muted text-sm mb-1">{{ __('ui.member_since') }}</p>
                                                <p class="mb-0 font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <div class="col-span-12 sm:col-span-6">
                                                <p class="text-muted text-sm mb-1">{{ __('ui.roles') }}</p>
                                                <p class="mb-0">
                                                    @forelse($user->roles as $role)
                                                        <x-admin::badge color="primary" :label="$role->name" class="me-1" />
                                                    @empty
                                                        <span class="text-muted">—</span>
                                                    @endforelse
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="javascript:void(0);" onclick="switchTab('profile-2')"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="ti ti-edit me-1"></i>{{ __('ui.edit') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Tab 2: Personal Details ── --}}
            <div class="{{ $activeTab === 'profile-2' ? 'block' : 'hidden' }} tab-pane" id="profile-2">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 lg:col-span-8 xl:col-span-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('ui.personal_details') }}</h5>
                            </div>
                            <div class="card-body">

                                {{-- Avatar upload --}}
                                <div class="text-center mb-6">
                                    <div class="inline-flex relative group mb-2">
                                        <img id="avatar-preview"
                                             class="rounded-full w-[90px] h-[90px] object-cover ring-4 ring-primary-500/20"
                                             src="{{ $avatarUrl }}" alt="{{ $user->name }}" />
                                    </div>
                                    <form method="POST" action="{{ route('profile.avatar') }}"
                                          enctype="multipart/form-data" id="avatar-form">
                                        @csrf
                                        <label for="avatar-input"
                                               class="btn btn-outline-secondary btn-sm cursor-pointer mt-1">
                                            <i class="ti ti-camera me-1"></i>{{ __('ui.change_avatar') }}
                                        </label>
                                        <input type="file" id="avatar-input" name="avatar"
                                               accept="image/jpeg,image/png,image/webp,image/gif"
                                               class="hidden" />
                                        <button type="submit" id="avatar-save"
                                                class="btn btn-primary btn-sm mt-1 hidden">
                                            <i class="ti ti-device-floppy me-1"></i>{{ __('ui.save') }}
                                        </button>
                                        @error('avatar')
                                            <div class="text-danger-500 text-xs mt-1">{{ $message }}</div>
                                        @enderror
                                    </form>
                                </div>

                                @if ($errors->has('name') || $errors->has('email'))
                                    <div class="mb-4 p-3 rounded bg-danger-500/10 border border-danger-500/20 text-sm text-danger-500">
                                        @foreach ($errors->get('name') as $error)<div>{{ $error }}</div>@endforeach
                                        @foreach ($errors->get('email') as $error)<div>{{ $error }}</div>@endforeach
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('profile.update') }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('ui.name') }}</label>
                                                <input type="text" name="name"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    value="{{ old('name', $user->name) }}" required />
                                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-span-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('ui.email') }} <span class="text-danger">*</span></label>
                                                <input type="email" name="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    value="{{ old('email', $user->email) }}" required />
                                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-span-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('ui.roles') }}</label>
                                                <div class="form-control bg-theme-bodybg dark:bg-themedark-bodybg" style="height:auto;min-height:38px;">
                                                    @forelse($user->roles as $role)
                                                        <x-admin::badge color="primary" :label="$role->name" class="me-1" />
                                                    @empty
                                                        <span class="text-muted text-sm">—</span>
                                                    @endforelse
                                                </div>
                                                <small class="text-muted text-xs">{{ __('ui.roles') }} {{ strtolower(__('ui.managed_by_admin') ?? 'managed by admin') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end mt-2">
                                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary me-2">{{ __('ui.cancel') }}</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-device-floppy me-1"></i>{{ __('ui.save_changes') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Tab 3: Change Password ── --}}
            <div class="{{ $activeTab === 'profile-3' ? 'block' : 'hidden' }} tab-pane" id="profile-3">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 lg:col-span-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('ui.change_password') }}</h5>
                            </div>
                            <div class="card-body">

                                @if ($errors->has('current_password') || ($errors->has('password') && !$errors->has('name')))
                                    <div class="mb-4 p-3 rounded bg-danger-500/10 border border-danger-500/20 text-sm text-danger-500">
                                        @foreach ($errors->get('current_password') as $error)<div>{{ $error }}</div>@endforeach
                                        @foreach ($errors->get('password') as $error)<div>{{ $error }}</div>@endforeach
                                    </div>
                                @endif

                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-6">
                                        <form method="POST" action="{{ route('profile.password') }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('ui.current_password') }}</label>
                                                <input type="password" name="current_password"
                                                    class="form-control @error('current_password') is-invalid @enderror"
                                                    autocomplete="current-password" required />
                                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('ui.new_password') }}</label>
                                                <input type="password" name="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    autocomplete="new-password" required />
                                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label">{{ __('ui.confirm_password') }}</label>
                                                <input type="password" name="password_confirmation"
                                                    class="form-control"
                                                    autocomplete="new-password" required />
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti ti-lock me-1"></i>{{ __('ui.change_password') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <h6 class="mb-3">{{ __('ui.new_password_must_contain') }}</h6>
                                        <ul class="rounded-lg divide-y divide-inherit border-theme-border dark:border-themedark-border *:py-3">
                                            <li class="!pt-0 flex items-center gap-2">
                                                <i class="ti ti-circle-check text-success-500 text-lg leading-none"></i>
                                                <span class="text-sm">{{ __('ui.at_least_8_chars') }}</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <i class="ti ti-circle-check text-success-500 text-lg leading-none"></i>
                                                <span class="text-sm">{{ __('ui.at_least_1_lower') }}</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <i class="ti ti-circle-check text-success-500 text-lg leading-none"></i>
                                                <span class="text-sm">{{ __('ui.at_least_1_upper') }}</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <i class="ti ti-circle-check text-success-500 text-lg leading-none"></i>
                                                <span class="text-sm">{{ __('ui.at_least_1_number') }}</span>
                                            </li>
                                            <li class="!pb-0 flex items-center gap-2">
                                                <i class="ti ti-circle-check text-success-500 text-lg leading-none"></i>
                                                <span class="text-sm">{{ __('ui.at_least_1_special') }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /.tab-content --}}
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input   = document.getElementById('avatar-input');
    var preview = document.getElementById('avatar-preview');
    var saveBtn = document.getElementById('avatar-save');
    if (!input) return;
    input.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            saveBtn.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });
});

function switchTab(tabId) {
    // Use AblePro's tab API if available, otherwise toggle manually
    const tabs   = document.querySelectorAll('#profileTabs .group');
    const panes  = document.querySelectorAll('.tab-pane');
    tabs.forEach(t => t.classList.remove('active'));
    panes.forEach(p => { p.classList.add('hidden'); p.classList.remove('block'); });
    const pane = document.getElementById(tabId);
    if (pane) { pane.classList.remove('hidden'); pane.classList.add('block'); }
    const anchor = document.querySelector(`[data-pc-target="${tabId}"]`);
    if (anchor) anchor.closest('.group').classList.add('active');
}
</script>
@endpush
@endsection
