@props([
    'action',
    'value'       => '',
    'clearUrl'    => null,
    'placeholder' => null,
])

<form method="GET" action="{{ $action }}" class="flex items-center gap-2">
    {{ $slot }}
    <div class="input-group w-auto">
        <input
            type="text"
            name="search"
            value="{{ $value }}"
            class="form-control"
            placeholder="{{ $placeholder ?? __('ui.search') . '...' }}"
            autocomplete="off"
        >
        <button type="submit" class="btn btn-outline-secondary">
            <i class="ti ti-search"></i>
        </button>
    </div>
    @if($clearUrl)
        <a href="{{ $clearUrl }}" class="btn btn-outline-danger btn-sm">
            <i class="ti ti-x"></i>
        </a>
    @endif
</form>
