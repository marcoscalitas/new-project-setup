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

@canany(['viewAny', 'delete'], \Modules\User\Models\User::class)
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-user-square"></use>
                </svg>
            </span>
            <span class="pc-mtext">{{ __('ui.users') }}</span>
            <span class="pc-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>
        <ul class="pc-submenu" style="display: none;">
            @can('viewAny', \Modules\User\Models\User::class)
                <li class="pc-item">
                    <a class="pc-link" href="{{ route('users.index') }}">{{ __('ui.users') }}</a>
                </li>
            @endcan
            @can('viewTrashed', \Modules\User\Models\User::class)
                <li class="pc-item">
                    <a class="pc-link" href="{{ route('users.trashed') }}">{{ __('ui.trashed_users') }}</a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

@canany(['viewAny', 'delete'], \Modules\Permission\Models\Role::class)
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-shield"></use>
                </svg>
            </span>
            <span class="pc-mtext">{{ __('ui.roles') }}</span>
            <span class="pc-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>
        <ul class="pc-submenu" style="display: none;">
            @can('viewAny', \Modules\Permission\Models\Role::class)
                <li class="pc-item">
                    <a class="pc-link" href="{{ route('roles.index') }}">{{ __('ui.roles') }}</a>
                </li>
            @endcan
            @can('viewTrashed', \Modules\Permission\Models\Role::class)
                <li class="pc-item">
                    <a class="pc-link" href="{{ route('roles.trashed') }}">{{ __('ui.trashed_roles') }}</a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

@canany(['viewAny', 'delete'], \Modules\Permission\Models\Permission::class)
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-lock-outline"></use>
                </svg>
            </span>
            <span class="pc-mtext">{{ __('ui.permissions') }}</span>
            <span class="pc-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>
        <ul class="pc-submenu" style="display: none;">
            @can('viewAny', \Modules\Permission\Models\Permission::class)
                <li class="pc-item">
                    <a class="pc-link" href="{{ route('permissions.index') }}">{{ __('ui.permissions') }}</a>
                </li>
            @endcan
            @can('viewTrashed', \Modules\Permission\Models\Permission::class)
                <li class="pc-item">
                    <a class="pc-link" href="{{ route('permissions.trashed') }}">{{ __('ui.trashed_permissions') }}</a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

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
