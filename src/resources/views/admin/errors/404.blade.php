<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
    dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">

<head>
    <title>404 | {{ config('app.name') }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    @include('admin.layouts.partials.head-styles')
</head>

<body>
    @include('admin.layouts.partials.preloader')

    <!-- [ Main Content ] start -->
    <div class="maintenance-block min-h-screen w-full flex items-center justify-center">
        <div class="container">
            <div class="card error-card bg-transparent dark:bg-transparent shadow-none border-none">
                <div class="card-body">
                    <div class="error-image-block">
                        <img class="img-fluid mx-auto"
                            src="{{ asset('admin/theme/images/pages/img-error-404.svg') }}"
                            alt="img" />
                    </div>
                    <div class="text-center">
                        <h1 class="mt-5"><b>Page Not Found</b></h1>
                        <p class="mt-2 mb-4 text-muted">The page you are looking was moved, removed,<br />renamed, or
                            might never exist!</p>
                        <a href="{{ url('/') }}" class="btn btn-primary mb-3">Go to home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
    @include('admin.layouts.partials.scripts')
    <script>
        layout_change('false');
        layout_theme_contrast_change('false');
        change_box_container('false');
        layout_caption_change('true');
        layout_rtl_change('false');
        preset_change('preset-1');
        main_layout_change('vertical');
    </script>
</body>

</html>
