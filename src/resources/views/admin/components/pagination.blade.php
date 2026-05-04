@if ($paginator->hasPages())
    @php $paginator->appends(request()->query()); @endphp
    <nav aria-label="Page navigation" class="mt-3">
        <ul class="flex *:*:inline-block *:*:px-3 *:*:py-1.5 *:border *:border-theme-border *:dark:border-themedark-border *:hover:bg-secondary-300/10">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="ltr:rounded-l-lg rtl:rounded-r-lg select-none !bg-secondary-500/10">
                    <span>Previous</span>
                </li>
            @else
                <li class="ltr:rounded-l-lg rtl:rounded-r-lg">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
                </li>
            @endif

            {{-- Page numbers --}}
            @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                @if ($i == $paginator->currentPage())
                    <li class="!bg-primary-500 !text-white !border-primary-500">
                        <span>{{ $i }}<span class="sr-only">(current)</span></span>
                    </li>
                @else
                    <li><a href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                @endif
            @endfor

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="ltr:rounded-r-lg rtl:rounded-l-lg">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                </li>
            @else
                <li class="ltr:rounded-r-lg rtl:rounded-l-lg select-none !bg-secondary-500/10">
                    <span>Next</span>
                </li>
            @endif

        </ul>
    </nav>
@endif
