@props([
    'paginator',
    'pageLengthOptions' => [5, 10, 15, 25, 50, 100],
])

{{-- Requires paginate() — lastPage() is not available with simplePaginate() --}}
@php
    $paginator->appends(request()->except($paginator->getPageName()));

    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();

    $window = 5;
    $half   = intdiv($window, 2);

    $start = max(1, $current - $half);
    $end   = min($last, $start + $window - 1);
    $start = max(1, $end - $window + 1);

    $pages = range($start, $end);
@endphp

@if ($paginator->total() > 0)
    <div class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
            @foreach (request()->except(['page', 'per_page']) as $name => $value)
                @if (is_array($value))
                    @foreach ($value as $item)
                        <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach

            <label for="per_page_{{ $paginator->getPageName() }}" class="mb-0 text-sm text-theme-body dark:text-themedark-body">
                {{ __('ui.rows_per_page') }}
            </label>
            <select
                id="per_page_{{ $paginator->getPageName() }}"
                name="per_page"
                class="form-select w-auto min-w-20"
                onchange="this.form.submit()"
            >
                @foreach ($pageLengthOptions as $option)
                    <option value="{{ $option }}" @selected((int) $paginator->perPage() === (int) $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </form>

        @if ($paginator->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="max-w-full overflow-x-auto">
                <ul class="flex w-max flex-wrap items-center gap-1">

                    {{-- First --}}
                    <li>
                        @if ($paginator->onFirstPage())
                            <span class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg select-none bg-secondary-500/10">‹ First</span>
                        @else
                            <a href="{{ $paginator->url(1) }}" class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">‹ First</a>
                        @endif
                    </li>

                    {{-- Previous --}}
                    <li>
                        @if ($paginator->onFirstPage())
                            <span class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg select-none bg-secondary-500/10">&lt;</span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">&lt;</a>
                        @endif
                    </li>

                    {{-- Page window --}}
                    @foreach ($pages as $page)
                        <li>
                            @if ($page === $current)
                                <span aria-current="page" class="inline-block px-3 py-1.5 border border-primary-500 rounded-lg bg-primary-500 text-white select-none">{{ $page }}</span>
                            @else
                                <a href="{{ $paginator->url($page) }}" class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach

                    {{-- Next --}}
                    <li>
                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">&gt;</a>
                        @else
                            <span class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg select-none bg-secondary-500/10">&gt;</span>
                        @endif
                    </li>

                    {{-- Last --}}
                    <li>
                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->url($last) }}" class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">Last ›</a>
                        @else
                            <span class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg select-none bg-secondary-500/10">Last ›</span>
                        @endif
                    </li>

                </ul>
            </nav>
        @endif
    </div>
@endif
