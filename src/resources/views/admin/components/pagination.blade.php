@if ($paginator->hasPages())
    @php
        $paginator->appends(request()->query());

        $current  = $paginator->currentPage();
        $last     = $paginator->lastPage();
        $window   = 2; // pages each side of current

        $pages = collect();

        // Always include first 2 and last 2
        foreach ([1, 2, $last - 1, $last] as $p) {
            if ($p >= 1 && $p <= $last) {
                $pages->push($p);
            }
        }

        // Include window around current
        for ($p = max(1, $current - $window); $p <= min($last, $current + $window); $p++) {
            $pages->push($p);
        }

        $pages = $pages->unique()->sort()->values();

        // Build final list: insert null (ellipsis) where gaps exist
        $items = collect();
        $prev  = null;
        foreach ($pages as $p) {
            if ($prev !== null && $p - $prev > 1) {
                $items->push(null); // ellipsis
            }
            $items->push($p);
            $prev = $p;
        }
    @endphp

    <nav aria-label="Page navigation" class="mt-3">
        <ul class="flex flex-wrap gap-1">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg select-none bg-secondary-500/10">Previous</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">Previous</a>
                </li>
            @endif

            {{-- Page numbers / ellipsis --}}
            @foreach ($items as $item)
                @if ($item === null)
                    <li>
                        <span class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg select-none">…</span>
                    </li>
                @elseif ($item === $current)
                    <li>
                        <span class="inline-block px-3 py-1.5 border border-primary-500 rounded-lg bg-primary-500 text-white select-none">
                            {{ $item }}<span class="sr-only">(current)</span>
                        </span>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->url($item) }}"
                           class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">{{ $item }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                       class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg hover:bg-secondary-300/10">Next</a>
                </li>
            @else
                <li>
                    <span class="inline-block px-3 py-1.5 border border-theme-border dark:border-themedark-border rounded-lg select-none bg-secondary-500/10">Next</span>
                </li>
            @endif

        </ul>
    </nav>
@endif
