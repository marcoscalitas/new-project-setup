@extends('admin.layouts.guest')

@section('title', __('ui.resend_verification_title'))

@section('content')
    <div class="auth-main relative">
        <div class="auth-wrapper v1 flex items-center w-full h-full min-h-screen">
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-[url('../images/authentication/img-auth-bg.jpg')] dark:bg-none dark:bg-themedark-bodybg">
                <div class="card sm:my-12 w-full max-w-[480px] shadow-none">
                    <div class="card-body !p-10">

                        @include('admin.layouts.partials.auth-brand')

                        <div class="mb-5">
                            <h3 class="font-semibold mb-1">{{ __('ui.resend_verification_title') }}</h3>
                            <p class="text-muted text-sm">{{ __('ui.resend_verification_subtitle') }}</p>
                        </div>

                        @if (session('status') === 'verification-link-sent')
                            <x-admin::alert type="success" class="mb-4">
                                {{ __('ui.verification_link_sent') }}
                            </x-admin::alert>
                        @endif

                        @if ($errors->any())
                            <x-admin::alert type="danger" class="mb-4">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </x-admin::alert>
                        @endif

                        <form method="POST" action="{{ route('web.auth.email.resend.send') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="email">
                                    {{ __('ui.email_address') }} <span class="text-danger-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="email@exemplo.com"
                                    value="{{ old('email', request('email')) }}" required autofocus />
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-full">
                                    {{ __('ui.resend_verification') }}
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="{{ route('login') }}" class="text-primary-500 text-sm">
                                &larr; {{ __('ui.back_to_login') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
