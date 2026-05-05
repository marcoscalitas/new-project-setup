@props([
    'paginator',
    'action' => url()->current(),
    'pageLengthOptions' => [5, 10, 15, 25, 50, 100],
])

@if ($paginator->total() > 0)
    <form method="GET" action="{{ $action }}" class="flex shrink-0 items-center gap-2">
        @foreach (request()->except(['page', 'per_page']) as $name => $value)
            @if (is_array($value))
                @foreach ($value as $item)
                    <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach

        <select
            id="per_page_{{ $paginator->getPageName() }}"
            name="per_page"
            class="form-select"
            style="width: 140px; min-width: 140px;"
            aria-label="{{ __('ui.entries_per_page') }}"
            onchange="this.form.submit()"
        >
            @foreach ($pageLengthOptions as $option)
                <option value="{{ $option }}" @selected((int) $paginator->perPage() === (int) $option)>
                    {{ $option }}
                </option>
            @endforeach
        </select>

        <label for="per_page_{{ $paginator->getPageName() }}" class="mb-0 whitespace-nowrap text-sm font-medium text-theme-headings dark:text-themedark-headings">
            {{ __('ui.entries_per_page') }}
        </label>
    </form>
@endif
