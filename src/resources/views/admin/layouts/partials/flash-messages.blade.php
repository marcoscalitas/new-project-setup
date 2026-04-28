<!-- [ Flash Messages ] start -->
@php
    $messages = [
        'success' => ['class' => 'success', 'icon' => 'check-circle', 'fade' => 'message-fade-out'],
        'info' => ['class' => 'info', 'icon' => 'info-circle', 'fade' => 'message-fade-out'],
        'error' => ['class' => 'danger', 'icon' => 'exclamation-circle', 'fade' => 'message-fade-out-err'],
        'warning' => ['class' => 'warning', 'icon' => 'exclamation-triangle', 'fade' => 'message-fade-out'],
    ];
@endphp

@foreach ($messages as $type => $config)
    @if (session()->has($type))
        <div class="alert alert-{{ $config['class'] }} {{ $config['fade'] }}">
            <span><i class="fas fa-{{ $config['icon'] }} fa-lg me-2"></i></span>
            {{ session($type) }}
        </div>
    @endif
@endforeach

{{-- @if ($errors->any())
    <div class="alert alert-danger message-fade-out-err">
        <span><i class="fas fa-exclamation-circle fa-lg me-2"></i></span>
        <strong>{{ __('ui.please_fix_errors') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif --}}
<!-- [ Flash Messages ] end -->
