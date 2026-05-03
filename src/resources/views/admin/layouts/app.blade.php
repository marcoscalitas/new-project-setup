<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
    dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">
<!-- [Head] start -->

<head>
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- [Theme] Restore from localStorage before CSS renders (prevents flash) — blocking, no defer -->
    <script src="{{ asset('admin/custom/js/theme-restore.js') }}"></script>
    @include('admin.layouts.partials.head-styles')
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body>
    @include('admin.layouts.partials.preloader')
    <!-- [ Sidebar Menu ] start -->
    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
            <div class="m-header flex items-center py-4 px-6 h-header-height">
                <a href="{{ route('home') }}" class="b-brand flex items-center gap-3">
                    <!-- ========   Change your logo from here   ============ -->
                    <span class="text-xl font-bold text-primary-500">{{ config('app.name') }}</span>
                </a>
            </div>
            <div class="navbar-content h-[calc(100vh_-_74px)] py-2.5">
                <div
                    class="card pc-user-card mx-[15px] mb-[15px] bg-theme-sidebaruserbg dark:bg-themedark-sidebaruserbg">
                    <div class="card-body !p-5">
                        <div class="flex items-center">
                            <img class="shrink-0 w-[45px] h-[45px] rounded-full object-cover"
                                src="{{ auth()->user()->getAvatarUrl(45) }}"
                                alt="{{ auth()->user()->name }}" />
                            <div class="ml-4 mr-2 grow">
                                <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                <small>{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</small>
                            </div>
                            <a class="shrink-0 btn btn-icon inline-flex btn-link-secondary" data-pc-toggle="collapse"
                                href="#pc_sidebar_userlink">
                                <svg class="pc-icon w-[22px] h-[22px]">
                                    <use xlink:href="#custom-sort-outline"></use>
                                </svg>
                            </a>
                        </div>
                        <div class="hidden pc-user-links" id="pc_sidebar_userlink">
                            <div class="pt-3 *:flex *:items-center *:py-2 *:gap-2.5 *:hover:text-primary-500">
                                <a href="{{ route('profile.edit') }}">
                                    <i class="text-lg leading-none ti ti-user"></i>
                                    <span>{{ __('ui.my_account') }}</span>
                                </a>
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                                    <i class="text-lg leading-none ti ti-power"></i>
                                    <span>{{ __('ui.logout') }}</span>
                                </a>
                                <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST"
                                    class="hidden">@csrf</form>
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="pc-navbar">
                    @include('admin.layouts.partials.sidebar')
                </ul>
            </div>
        </div>
    </nav>
    <!-- [ Sidebar Menu ] end -->
    <!-- [ Header Topbar ] start -->
    <header class="pc-header">
        <div class="header-wrapper flex max-sm:px-[15px] px-[25px] grow"><!-- [Mobile Media Block] start -->
            <div class="me-auto pc-mob-drp">
                <ul class="inline-flex *:min-h-header-height *:inline-flex *:items-center">
                    <!-- ======= Menu collapse Icon ===== -->
                    <li class="pc-h-item pc-sidebar-collapse max-lg:hidden lg:inline-flex">
                        <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0" id="sidebar-hide">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup lg:hidden">
                        <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0" id="mobile-collapse">
                            <i class="ti ti-menu-2 text-2xl leading-none"></i>
                        </a>
                    </li>
                    <li class="pc-h-item max-md:hidden md:inline-flex">
                        <form class="form-search relative">
                            <i class="search-icon absolute top-[14px] left-[15px]">
                                <svg class="pc-icon w-4 h-4">
                                    <use xlink:href="#custom-search-normal-1"></use>
                                </svg>
                            </i>
                            <input type="search" class="form-control px-2.5 pr-3 pl-10 w-[198px] leading-none"
                                placeholder="Ctrl + K" />
                        </form>
                    </li>
                </ul>
            </div>
            <!-- [Mobile Media Block end] -->
            <div class="ms-auto">
                <ul class="inline-flex *:min-h-header-height *:inline-flex *:items-center">
                    <li class="dropdown pc-h-item">
                        <a class="pc-head-link dropdown-toggle me-0" data-pc-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sun-1"></use>
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                            <a href="#!" class="dropdown-item"
                                onclick="layout_change('dark'); document.documentElement.setAttribute('data-theme','dark'); localStorage.setItem('theme','dark');">
                                <svg class="pc-icon w-[18px] h-[18px]">
                                    <use xlink:href="#custom-moon"></use>
                                </svg>
                                <span>Dark</span>
                            </a>
                            <a href="#!" class="dropdown-item"
                                onclick="layout_change('light'); document.documentElement.setAttribute('data-theme','light'); localStorage.setItem('theme','light');">
                                <svg class="pc-icon w-[18px] h-[18px]">
                                    <use xlink:href="#custom-sun-1"></use>
                                </svg>
                                <span>Light</span>
                            </a>
                            <a href="#!" class="dropdown-item"
                                onclick="layout_change_default(); var _t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'; document.documentElement.setAttribute('data-theme',_t); localStorage.setItem('theme',_t);">
                                <svg class="pc-icon w-[18px] h-[18px]">
                                    <use xlink:href="#custom-monitor"></use>
                                </svg>
                                <span>Default</span>
                            </a>
                        </div>
                    </li>
                    <li class="dropdown pc-h-item">
                        <a class="pc-head-link dropdown-toggle me-0" data-pc-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-language"></use>
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                            <a href="{{ route('locale.switch', 'en') }}"
                                class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                                <span>English <small>(EN)</small></span>
                                @if (app()->getLocale() === 'en')
                                    <i class="ti ti-check ms-auto"></i>
                                @endif
                            </a>
                            <a href="{{ route('locale.switch', 'pt') }}"
                                class="dropdown-item {{ app()->getLocale() === 'pt' ? 'active' : '' }}">
                                <span>Português <small>(PT)</small></span>
                                @if (app()->getLocale() === 'pt')
                                    <i class="ti ti-check ms-auto"></i>
                                @endif
                            </a>
                        </div>
                    </li>
                    <li class="dropdown pc-h-item">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-pc-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-setting-2"></use>
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="ti ti-user"></i>
                                <span>{{ __('ui.my_account') }}</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="ti ti-settings"></i>
                                <span>{{ __('ui.settings') }}</span>
                            </a>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('header-logout-form').submit();"
                                class="dropdown-item">
                                <i class="ti ti-power"></i>
                                <span>{{ __('ui.logout') }}</span>
                            </a>
                            <form id="header-logout-form" action="{{ route('logout') }}" method="POST"
                                class="hidden">@csrf</form>
                        </div>
                    </li>
                    <li class="pc-h-item">
                        <a href="#" class="pc-head-link me-0" data-pc-toggle="offcanvas"
                            data-pc-target="#announcement" aria-controls="announcement">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-flash"></use>
                            </svg>
                        </a>
                    </li>
                    @php $unreadNotifications = auth()->user()->unreadNotifications->take(5); @endphp
                    <li class="dropdown pc-h-item">
                        <a class="pc-head-link dropdown-toggle me-0" data-pc-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-notification"></use>
                            </svg>
                            @if ($unreadNotifications->count() > 0)
                                <span class="badge bg-success-500 text-white rounded-full z-10 absolute right-0 top-0">
                                    {{ $unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown p-2">
                            <div class="dropdown-header flex items-center justify-between py-4 px-5">
                                <h5 class="m-0">{{ __('ui.notifications') }}</h5>
                            </div>
                            <div class="dropdown-body header-notification-scroll relative py-4 px-5"
                                style="max-height: calc(100vh - 215px)">
                                @forelse($unreadNotifications as $notification)
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="flex gap-4">
                                                <div class="shrink-0">
                                                    <svg class="pc-icon text-primary-500 w-[22px] h-[22px]">
                                                        <use xlink:href="#custom-notification"></use>
                                                    </svg>
                                                </div>
                                                <div class="grow">
                                                    <span
                                                        class="float-end text-sm text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                                    <p class="mb-0">
                                                        {{ $notification->data['message'] ?? 'New notification' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-muted py-4 mb-0">No new notifications</p>
                                @endforelse
                            </div>
                        </div>
                    </li>
                    <li class="dropdown pc-h-item header-user-profile">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-pc-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" data-pc-auto-close="outside"
                            aria-expanded="false">
                            <img src="{{ auth()->user()->getAvatarUrl(80) }}"
                                alt="{{ auth()->user()->name }}" class="user-avtar w-10 h-10 rounded-full" />
                        </a>
                        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-2">
                            <div class="dropdown-header flex items-center justify-between py-4 px-5">
                                <h5 class="m-0">{{ __('ui.my_profile') }}</h5>
                            </div>
                            <div class="profile-notification-scroll position-relative"
                                style="max-height: calc(100vh - 225px)">
                                <div class="dropdown-body py-4 px-5">
                                    <div class="flex mb-1 items-center">
                                        <div class="shrink-0">
                                            <img src="{{ auth()->user()->getAvatarUrl(80) }}"
                                                alt="{{ auth()->user()->name }}" class="w-10 rounded-full" />
                                        </div>
                                        <div class="grow ms-3">
                                            <h6 class="mb-1">{{ auth()->user()->name }}</h6>
                                            <span>{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                    <hr class="border-secondary-500/10 my-4" />
                                    <p class="text-span mb-3">{{ __('ui.manage') }}</p>
                                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                        <span>
                                            <svg class="pc-icon text-muted me-2 inline-block">
                                                <use xlink:href="#custom-setting-outline"></use>
                                            </svg>
                                            <span>{{ __('ui.settings') }}</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                        <span>
                                            <svg class="pc-icon text-muted me-2 inline-block">
                                                <use xlink:href="#custom-lock-outline"></use>
                                            </svg>
                                            <span>{{ __('ui.change_password') }}</span>
                                        </span>
                                    </a>
                                    <hr class="border-secondary-500/10 my-4" />
                                    <div class="grid mb-3">
                                        <button
                                            onclick="event.preventDefault(); document.getElementById('profile-logout-form').submit();"
                                            class="btn btn-primary-500 flex items-center justify-center">
                                            <svg class="pc-icon me-2 w-[22px] h-[22px]">
                                                <use xlink:href="#custom-logout-1-outline"></use>
                                            </svg>
                                            {{ __('ui.logout') }}
                                        </button>
                                        <form id="profile-logout-form" action="{{ route('logout') }}" method="POST"
                                            class="hidden">@csrf</form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <div class="offcanvas pc-announcement-offcanvas offcanvas-end" tabindex="-1" id="announcement"
        aria-labelledby="announcementLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="announcementLabel">{{ __('ui.whats_new_announcement') }}</h5>
            <button data-pc-dismiss="#announcement"
                class="text-lg flex items-center justify-center rounded w-7 h-7 text-secondary-500 hover:bg-danger-500/10 hover:text-danger-500">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body announcement-scroll-block">
            <p class="mb-3">{{ __('ui.today') }}</p>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="items-center flex wrap gap-2 mb-3">
                        <div class="badge text-success-500 bg-success-500/10 text-sm">News</div>
                        <p class="mb-0">{{ config('app.name') }}</p>
                    </div>
                    <h5 class="mb-3">Welcome to {{ config('app.name') }}</h5>
                    <p class="text-muted mb-3">Your admin panel is ready.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <ul class="breadcrumb">
                        @yield('breadcrumb')
                    </ul>
                    <div class="page-header-title">
                        <h2 class="mb-0">@yield('page-title', 'Dashboard')</h2>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            @include('admin.layouts.partials.flash-messages')

            <!-- [ Main Content ] start -->
            @yield('content')
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <!-- [ Main Content ] end -->
    <footer class="pc-footer">
        <div class="footer-wrapper container-fluid mx-10">
            <div class="grid grid-cols-12 gap-1.5">
                <div class="col-span-12 sm:col-span-6 my-1">
                    <p class="m-0">
                        &copy; {{ date('Y') }} {{ config('app.name') }}
                    </p>
                </div>
            </div>
        </div>
    </footer>
    @include('admin.layouts.partials.scripts')

    <script src="{{ asset('admin/custom/js/theme-customizer.js') }}" defer></script>

    <!-- [Page Specific JS] start -->
    @stack('scripts')
    <!-- [Page Specific JS] end -->
    @include('admin.layouts.partials.theme-customizer')
    <x-admin::delete-modal />
    <x-admin::restore-modal />
</body>
<!-- [Body] end -->

</html>
