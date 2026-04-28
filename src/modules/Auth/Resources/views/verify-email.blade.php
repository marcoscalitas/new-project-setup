@extends('admin.layouts.guest')

@section('title', __('ui.verify_email_title'))

@section('content')
    <div class="auth-main relative">
        <div class="auth-wrapper v1 flex items-center w-full h-full min-h-screen">
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-[url('../images/authentication/img-auth-bg.jpg')] dark:bg-none dark:bg-themedark-bodybg">
                <div class="card sm:my-12 w-full max-w-[480px] shadow-none">
                    <div class="card-body !p-10">

                        @include('admin.layouts.partials.auth-brand')

                        <div class="mb-5">
                            <h3 class="font-semibold mb-1">{{ __('ui.verify_email_title') }}</h3>
                            <p class="text-muted text-sm">{{ __('ui.verify_email_subtitle') }}</p>
                        </div>

                        @if (session('status') == 'verification-link-sent')
                            <div
                                class="mb-4 p-3 rounded bg-success-500/10 border border-success-500/20 text-sm text-success-500">
                                {{ __('ui.verification_link_sent') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <div class="grid">
                                <button type="submit" class="btn btn-primary">{{ __('ui.resend_verification') }}</button>
                            </div>
                        </form>

                        <div class="relative my-5">
                            <div aria-hidden="true" class="absolute flex inset-0 items-center">
                                <div class="w-full border-t border-theme-border dark:border-themedark-border"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="px-4 bg-theme-cardbg dark:bg-themedark-cardbg">{{ __('ui.or') }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <div class="grid">
                                <button type="submit"
                                    class="btn border border-theme-border dark:border-themedark-border text-theme-bodycolor dark:text-themedark-bodycolor">{{ __('ui.logout') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
