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
    <li class="pc-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
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
@can('viewAny', \Modules\Permission\Models\Role::class)
    <li class="pc-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
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
@can('viewAny', \Modules\Permission\Models\Permission::class)
    <li class="pc-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
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
<li class="pc-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
    <a href="{{ route('notifications.index') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-notification"></use>
            </svg>
        </span>
        <span class="pc-mtext">{{ __('ui.notifications') }}</span>
    </a>
</li>
@can('viewAny', \Spatie\Activitylog\Models\Activity::class)
    <li class="pc-item {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
        <a href="{{ route('activity-log.index') }}" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-data"></use>
                </svg>
            </span>
            <span class="pc-mtext">{{ __('ui.activity_log') }}</span>
        </a>
    </li>
@endcan
<li class="pc-item {{ request()->routeIs('exports.*') ? 'active' : '' }}">
    <a href="{{ route('exports.index') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-document-download"></use>
            </svg>
        </span>
        <span class="pc-mtext">{{ __('ui.exports') }}</span>
    </a>
</li>

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
