<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="preset-1" data-pc-sidebar-caption="true"
    data-pc-layout="vertical" data-pc-direction="ltr" dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">
<!-- [Head] start -->

<head>
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @include('admin.layouts.partials.head-styles')
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body>
    @include('admin.layouts.partials.preloader')

    @yield('content')

    <!-- [ Main Content ] end -->
    @include('admin.layouts.partials.scripts')

    <script src="{{ asset('admin/custom/js/toggle-password.js') }}"></script>
    <script src="{{ asset('admin/custom/js/guest-init.js') }}"></script>

    @include('admin.layouts.partials.theme-customizer', ['withLanguage' => true])
</body>
<!-- [Body] end -->

</html>
