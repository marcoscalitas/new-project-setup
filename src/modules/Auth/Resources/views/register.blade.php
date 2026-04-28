@extends('admin.layouts.guest')

@section('title', __('ui.register'))

@section('content')
    <div class="auth-main relative">
        <div class="auth-wrapper v1 flex items-center w-full h-full min-h-screen">
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-[url('../images/authentication/img-auth-bg.jpg')] dark:bg-none dark:bg-themedark-bodybg">
                <div class="card sm:my-12 w-full max-w-[480px] shadow-none">
                    <div class="card-body !p-10">

                        @include('admin.layouts.partials.auth-brand')

                        <h3 class="font-semibold mb-5">{{ __('ui.sign_up_with_email') }}</h3>

                        @if ($errors->any())
                            <div
                                class="mb-4 p-3 rounded bg-danger-500/10 border border-danger-500/20 text-sm text-danger-500">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="grid grid-cols-12 gap-x-4">
                                <div class="col-span-12 sm:col-span-6 mb-4">
                                    <label class="form-label" for="name">
                                        {{ __('ui.first_name') }} <span class="text-danger-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="{{ __('ui.first_name') }}" value="{{ old('name') }}" required autofocus />
                                </div>
                                <div class="col-span-12 sm:col-span-6 mb-4">
                                    <label class="form-label" for="last_name">{{ __('ui.last_name') }}</label>
                                    <input type="text" name="last_name" id="last_name" class="form-control"
                                        placeholder="{{ __('ui.last_name') }}" value="{{ old('last_name') }}" />
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="reg_email">
                                    {{ __('ui.email_address') }} <span class="text-danger-500">*</span>
                                </label>
                                <input type="email" name="email" id="reg_email" class="form-control"
                                    placeholder="email@exemplo.com" value="{{ old('email') }}" required />
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="reg_password">
                                    {{ __('ui.password') }} <span class="text-danger-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password" id="reg_password" class="form-control pr-10"
                                        placeholder="{{ __('ui.password') }}" required />
                                    <button type="button" onclick="togglePassword('reg_password')"
                                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%)"
                                        class="text-secondary-500 hover:text-primary-500">
                                        <i class="ti ti-eye text-lg" id="reg_password-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="reg_password_confirm">
                                    {{ __('ui.confirm_password_label') }} <span class="text-danger-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="reg_password_confirm"
                                        class="form-control pr-10"
                                        placeholder="{{ __('ui.confirm_password_label') }}" required />
                                    <button type="button" onclick="togglePassword('reg_password_confirm')"
                                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%)"
                                        class="text-secondary-500 hover:text-primary-500">
                                        <i class="ti ti-eye text-lg" id="reg_password_confirm-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input input-primary" type="checkbox" id="terms" required />
                                <label class="form-check-label text-muted" for="terms">{{ __('ui.terms_agree') }}</label>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary w-full">{{ __('ui.sign_up') }}</button>
                            </div>
                        </form>

                        <div class="flex justify-between items-center flex-wrap mt-4">
                            <span class="text-sm font-medium">{{ __('ui.already_have_account') }}</span>
                            <a href="{{ route('login') }}" class="text-primary-500 text-sm">{{ __('ui.login_here') }}</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
