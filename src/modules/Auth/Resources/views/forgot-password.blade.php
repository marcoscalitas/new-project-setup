@extends('admin.layouts.guest')

@section('title', __('ui.forgot_password_title'))

@section('content')
    <div class="auth-main relative">
        <div class="auth-wrapper v1 flex items-center w-full h-full min-h-screen">
            <div
                class="auth-form flex items-center justify-center grow flex-col min-h-screen bg-cover relative p-6 bg-[url('../images/authentication/img-auth-bg.jpg')] dark:bg-none dark:bg-themedark-bodybg">
                <div class="card sm:my-12 w-full max-w-[480px] shadow-none">
                    <div class="card-body !p-10">

                        @include('admin.layouts.partials.auth-brand')

                        <div class="flex justify-between items-center mb-5">
                            <h3 class="font-semibold mb-0">{{ __('ui.forgot_password_title') }}</h3>
                            <a href="{{ route('login') }}" class="text-primary-500 text-sm">{{ __('ui.back_to_login') }}</a>
                        </div>

                        @if (session('status'))
                            <div
                                class="mb-4 p-3 rounded bg-success-500/10 border border-success-500/20 text-sm text-success-500">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div
                                class="mb-4 p-3 rounded bg-danger-500/10 border border-danger-500/20 text-sm text-danger-500">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="fp_email">
                                    {{ __('ui.email_address') }} <span class="text-danger-500">*</span>
                                </label>
                                <input type="email" name="email" id="fp_email" class="form-control"
                                    placeholder="email@exemplo.com" value="{{ old('email') }}" required autofocus />
                            </div>
                            <p class="text-sm text-muted mb-4">{{ __('ui.check_spam_box') }}</p>
                            <div class="grid">
                                <button type="submit" class="btn btn-primary">{{ __('ui.send_reset_email') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
