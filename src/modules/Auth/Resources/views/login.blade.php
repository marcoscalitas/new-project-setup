@extends('admin.layouts.guest')

@section('title', __('ui.login'))

@section('content')
    <div class="auth-main relative">
        <div class="auth-wrapper v1 flex items-center w-full h-full min-h-screen">
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-[url('../images/authentication/img-auth-bg.jpg')] dark:bg-none dark:bg-themedark-bodybg">
                <div class="card sm:my-12 w-full max-w-[480px] shadow-none">
                    <div class="card-body !p-10">

                        @include('admin.layouts.partials.auth-brand')

                        <h3 class="font-semibold mb-5">{{ __('ui.login_with_email') }}</h3>

                        @if (session('status') === 'verification-link-sent')
                            <div
                                class="mb-4 p-3 rounded bg-success-500/10 border border-success-500/20 text-sm text-success-500">
                                {{ __('ui.verification_link_sent') }}
                            </div>
                        @endif

                        @if ($errors->has('activation'))
                            <div
                                class="mb-4 p-3 rounded bg-primary-500/10 border border-primary-500/20 text-sm text-primary-500">
                                {{ $errors->first('activation') }}
                                <div class="mt-2">
                                    <a href="{{ route('web.auth.email.resend') }}?email={{ urlencode(old('email', '')) }}"
                                        class="underline hover:no-underline">
                                        {{ __('ui.did_not_receive_email') }}
                                    </a>
                                </div>
                            </div>
                        @elseif ($errors->any())
                            <div
                                class="mb-4 p-3 rounded bg-danger-500/10 border border-danger-500/20 text-sm text-danger-500">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="email">
                                    {{ __('ui.email_address') }} <span class="text-danger-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="email@exemplo.com" value="{{ old('email') }}" required autofocus />
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="password">
                                    {{ __('ui.password') }} <span class="text-danger-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" class="form-control pr-10"
                                        placeholder="{{ __('ui.password') }}" required />
                                    <button type="button" onclick="togglePassword('password')"
                                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%)"
                                        class="text-secondary-500 hover:text-primary-500">
                                        <i class="ti ti-eye text-lg" id="password-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex mt-1 justify-between items-center flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input input-primary" type="checkbox" name="remember"
                                        id="remember" />
                                    <label class="form-check-label text-muted" for="remember">{{ __('ui.remember_me') }}</label>
                                </div>
                                <a href="{{ route('password.request') }}" class="text-primary-500 text-sm">
                                    {{ __('ui.forgot_password') }}
                                </a>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-full">{{ __('ui.login') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
