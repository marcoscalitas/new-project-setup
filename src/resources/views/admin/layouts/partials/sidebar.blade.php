<li class="pc-item pc-caption">
    <label>{{ __('ui.navigation') }}</label>
</li>
<li class="pc-item {{ request()->routeIs('home') ? 'active' : '' }}">
    <a href="{{ route('home') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-status-up"></use>
            </svg>
        </span>
        <span class="pc-mtext">{{ __('ui.dashboard') }}</span>
    </a>
</li>

<li class="pc-item pc-caption">
    <label>{{ __('ui.admin_panel') }}</label>
    <svg class="pc-icon">
        <use xlink:href="#custom-layer"></use>
    </svg>
</li>
@can('viewAny', \Modules\User\Models\User::class)
    <li class="pc-item {{ request()->routeIs('users.index') || request()->routeIs('users.show') || request()->routeIs('users.create') || request()->routeIs('users.edit') ? 'active' : '' }}">
        <a href="{{ route('users.index') }}" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-user-square"></use>
                </svg>
            </span>
            <span class="pc-mtext">{{ __('ui.users') }}</span>
        </a>
    </li>
@endcan
@can('delete', \Modules\User\Models\User::class)
    <li class="pc-item {{ request()->routeIs('users.trashed') ? 'active' : '' }}">
        <a href="{{ route('users.trashed') }}" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-trash text-lg leading-none"></i>
            </span>
            <span class="pc-mtext">{{ __('ui.trashed_users') }}</span>
        </a>
    </li>
@endcan
@can('viewAny', \Modules\Permission\Models\Role::class)
    <li class="pc-item {{ request()->routeIs('roles.index') || request()->routeIs('roles.show') || request()->routeIs('roles.create') || request()->routeIs('roles.edit') ? 'active' : '' }}">
        <a href="{{ route('roles.index') }}" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-shield"></use>
                </svg>
            </span>
            <span class="pc-mtext">{{ __('ui.roles') }}</span>
        </a>
    </li>
@endcan
@can('delete', \Modules\Permission\Models\Role::class)
    <li class="pc-item {{ request()->routeIs('roles.trashed') ? 'active' : '' }}">
        <a href="{{ route('roles.trashed') }}" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-trash text-lg leading-none"></i>
            </span>
            <span class="pc-mtext">{{ __('ui.trashed_roles') }}</span>
        </a>
    </li>
@endcan
@can('viewAny', \Modules\Permission\Models\Permission::class)
    <li class="pc-item {{ request()->routeIs('permissions.index') || request()->routeIs('permissions.show') || request()->routeIs('permissions.create') || request()->routeIs('permissions.edit') ? 'active' : '' }}">
        <a href="{{ route('permissions.index') }}" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-lock-outline"></use>
                </svg>
            </span>
            <span class="pc-mtext">{{ __('ui.permissions') }}</span>
        </a>
    </li>
@endcan
@can('delete', \Modules\Permission\Models\Permission::class)
    <li class="pc-item {{ request()->routeIs('permissions.trashed') ? 'active' : '' }}">
        <a href="{{ route('permissions.trashed') }}" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-trash text-lg leading-none"></i>
            </span>
            <span class="pc-mtext">{{ __('ui.trashed_permissions') }}</span>
        </a>
    </li>
@endcan

<li class="pc-item pc-caption">
    <label>{{ __('ui.account') }}</label>
    <svg class="pc-icon">
        <use xlink:href="#custom-layer"></use>
    </svg>
</li>
<li class="pc-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <a href="{{ route('profile.edit') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-profile-circle"></use>
            </svg>
        </span>
        <span class="pc-mtext">{{ __('ui.profile') }}</span>
    </a>
</li>
