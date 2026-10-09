@if ($paginator->hasPages())
    <nav class="mt-14 flex items-center justify-between gap-4 border-t border-line pt-8" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary btn-sm" aria-disabled="true"><x-icon name="arrow-left" class="btn-icon" /> Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm"><x-icon name="arrow-left" class="btn-icon" /> Previous</a>
        @endif

        <ul class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="px-2 text-ink-subtle" aria-hidden="true">{{ $element }}</li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="chip" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="chip" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>

        <p class="mono-label sm:hidden">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm">Next <x-icon name="arrow-right" class="btn-icon" /></a>
        @else
            <span class="btn btn-secondary btn-sm" aria-disabled="true">Next <x-icon name="arrow-right" class="btn-icon" /></span>
        @endif
    </nav>
@endif
