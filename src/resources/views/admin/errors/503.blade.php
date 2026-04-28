<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
    dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">

<head>
    <title>503 | {{ config('app.name') }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    @include('admin.layouts.partials.head-styles')
</head>

<body>
    @include('admin.layouts.partials.preloader')

    <!-- [ Main Content ] start -->
    <div
        class="maintenance-block construction-card-1 min-h-screen w-full flex items-center justify-center bg-[url('/admin/theme/images/pages/img-cunstruct-1-bg.png')] bg-[length:100%] bg-no-repeat">
        <div class="container">
            <div class="card construction-card bg-transparent shadow-none border-none">
                <div class="card-body">
                    <div class="construction-image-block">
                        <div class="grid grid-cols-12 gap-12 construction-card-bottom">
                            <div class="col-span-12 md:col-span-6 self-center">
                                <div class="text-center">
                                    <h1 class="mt-4"><b>Under Construction</b></h1>
                                    <p class="my-4 text-muted">Hey! Please check out this site later. We are doing
                                        <br />some maintenance on it right now.
                                    </p>
                                    <a href="{{ url('/') }}" class="btn btn-primary mb-3">Back To Home</a>
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-6 self-center">
                                <img class="img-fluid relative z-20"
                                    src="{{ asset('admin/theme/images/pages/img-cunstruct-1.svg') }}"
                                    alt="img" />
                            </div>
                            <div class="col-span-12 relative">
                                <img class="img-fluid w-100 absolute inset-x-0 bottom-0 z-10"
                                    src="{{ asset('admin/theme/images/pages/img-cunstruct-1-bottom.svg') }}"
                                    alt="img" />
                            </div>
                        </div>
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
