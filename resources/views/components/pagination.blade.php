@props([
    'page',
    'pages',
    'total',
    'from',
    'to',
    'query' => [],
])
{{--
    One caller, kept deliberately. The alternative is two dozen lines of
    current/disabled semantics inside the page template, and the prop contract
    here is pure data that would drop into any list view unchanged. That is the
    honest justification -- "it keeps the page template readable" -- and the
    README says so rather than dressing it up as reuse.

    There is no ellipsis branch. Forty-four orders at twenty per page is three
    pages, so a branch for "…" would be unreachable code, which a reviewer finds
    and correctly reads as copied from somewhere else.

    Previous on page 1 is a <span>, not <a href="#">: a disabled control should
    leave the tab order, not sit in it doing nothing.
--}}
<div {{ $attributes->merge(['class' => 'pager']) }}>
    <p class="pager__count">Showing {{ $from }}–{{ $to }} of {{ $total }} orders</p>

    <nav class="pager__nav" aria-label="Pagination">
        @if ($page > 1)
            <a class="pager__item" rel="prev" href="{{ orders_url(['page' => $page - 1] + $query) }}">‹ Previous</a>
        @else
            <span class="pager__item" data-disabled>‹ Previous</span>
        @endif

        @for ($n = 1; $n <= $pages; $n++)
            @if ($n === $page)
                <span class="pager__item" aria-current="page">{{ $n }}</span>
            @else
                <a class="pager__item" href="{{ orders_url(['page' => $n] + $query) }}">
                    <span class="sr-only">Page </span>{{ $n }}
                </a>
            @endif
        @endfor

        @if ($page < $pages)
            <a class="pager__item" rel="next" href="{{ orders_url(['page' => $page + 1] + $query) }}">Next ›</a>
        @else
            <span class="pager__item" data-disabled>Next ›</span>
        @endif
    </nav>
</div>
