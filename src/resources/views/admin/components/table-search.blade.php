@props([
    'action',
    'value'       => '',
    'clearUrl'    => null,
    'placeholder' => null,
])

<form method="GET" action="{{ $action }}" class="flex items-center gap-2">
    {{ $slot }}
    <div class="form-search relative flex-1">
        <i class="search-icon absolute top-[14px] left-[15px]">
            <svg class="pc-icon w-4 h-4">
                <use xlink:href="#custom-search-normal-1"></use>
            </svg>
        </i>
        <input
            type="search"
            name="search"
            value="{{ $value }}"
            class="form-control px-2.5 pr-3 pl-10 w-full leading-none"
            placeholder="{{ $placeholder ?? __('ui.search') . '...' }}"
            autocomplete="off"
        >
    </div>
    <button type="submit" class="btn btn-icon btn-primary">
        <i class="ti ti-search"></i>
    </button>
    @if($clearUrl)
        <a href="{{ $clearUrl }}" class="btn btn-icon btn-outline-danger">
            <i class="ti ti-x"></i>
        </a>
    @endif
</form>
