@extends('admin.layouts.guest')

@section('title', __('ui.reset_password'))

@section('content')
    <div class="auth-main relative">
        <div class="auth-wrapper v1 flex items-center w-full h-full min-h-screen">
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-[url('../images/authentication/img-auth-bg.jpg')] dark:bg-none dark:bg-themedark-bodybg">
                <div class="card sm:my-12 w-full max-w-[480px] shadow-none">
                    <div class="card-body !p-10">

                        @include('admin.layouts.partials.auth-brand')

                        <div class="mb-5">
                            <h3 class="font-semibold mb-1">{{ __('ui.reset_password') }}</h3>
                            <p class="text-muted text-sm">{{ __('ui.choose_new_password') }}</p>
                        </div>

                        @if ($errors->any())
                            <div
                                class="mb-4 p-3 rounded bg-danger-500/10 border border-danger-500/20 text-sm text-danger-500">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}" />
                            <input type="hidden" name="email" value="{{ $email ?? old('email') }}" />
                            <div class="mb-4">
                                <label class="form-label" for="rp_password">
                                    {{ __('ui.password') }} <span class="text-danger-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password" id="rp_password" class="form-control pr-10"
                                        placeholder="{{ __('ui.password') }}" required autofocus />
                                    <button type="button" onclick="togglePassword('rp_password')"
                                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%)"
                                        class="text-secondary-500 hover:text-primary-500">
                                        <i class="ti ti-eye text-lg" id="rp_password-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="rp_password_confirm">
                                    {{ __('ui.confirm_password_label') }} <span class="text-danger-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="rp_password_confirm"
                                        class="form-control pr-10"
                                        placeholder="{{ __('ui.confirm_password_label') }}" required />
                                    <button type="button" onclick="togglePassword('rp_password_confirm')"
                                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%)"
                                        class="text-secondary-500 hover:text-primary-500">
                                        <i class="ti ti-eye text-lg" id="rp_password_confirm-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="grid mt-2">
                                <button type="submit" class="btn btn-primary">{{ __('ui.update_password') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
