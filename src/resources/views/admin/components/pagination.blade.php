{{-- Requires paginate() — lastPage() is not available with simplePaginate() --}}
@if ($paginator->hasPages())
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

    <nav role="navigation" aria-label="Pagination Navigation" class="mt-3">
        <ul class="inline-flex items-center rounded-lg overflow-hidden border border-theme-border dark:border-themedark-border">

            {{-- First --}}
            <li>
                @if ($paginator->onFirstPage())
                    <span class="inline-block px-3 py-2 select-none opacity-50 bg-secondary-500/10">‹ First</span>
                @else
                    <a href="{{ $paginator->url(1) }}" class="inline-block px-3 py-2 hover:bg-secondary-300/10">‹ First</a>
                @endif
            </li>

            {{-- Previous --}}
            <li class="border-l border-theme-border dark:border-themedark-border">
                @if ($paginator->onFirstPage())
                    <span class="inline-block px-3 py-2 select-none opacity-50 bg-secondary-500/10">&lt;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-block px-3 py-2 hover:bg-secondary-300/10">&lt;</a>
                @endif
            </li>

            {{-- Page window --}}
            @foreach ($pages as $page)
                <li class="border-l border-theme-border dark:border-themedark-border">
                    @if ($page === $current)
                        <span aria-current="page" class="inline-block px-4 py-2 bg-primary-500 text-white select-none">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="inline-block px-4 py-2 hover:bg-secondary-300/10">{{ $page }}</a>
                    @endif
                </li>
            @endforeach

            {{-- Next --}}
            <li class="border-l border-theme-border dark:border-themedark-border">
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-block px-3 py-2 hover:bg-secondary-300/10">&gt;</a>
                @else
                    <span class="inline-block px-3 py-2 select-none opacity-50 bg-secondary-500/10">&gt;</span>
                @endif
            </li>

            {{-- Last --}}
            <li class="border-l border-theme-border dark:border-themedark-border">
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->url($last) }}" class="inline-block px-3 py-2 hover:bg-secondary-300/10">Last ›</a>
                @else
                    <span class="inline-block px-3 py-2 select-none opacity-50 bg-secondary-500/10">Last ›</span>
                @endif
            </li>

        </ul>
    </nav>
@endif
