@props([
    'title',
    'body' => null,
    'filters' => [],
    'clear' => null,
    'colspan' => 6,
])
{{--
    Renders inside a single <td colspan>, so the <thead> and <colgroup> survive.
    The columns staying put is the point: you can see what you were looking at,
    and the page does not jump when the result set comes back.

    It is a diff of your query, not an apology and not an illustration. Each
    token removes exactly one constraint, so you can back out of a dead end one
    step at a time without going through the filter bar again.

    index.php picks the copy; there is no conditional here beyond whether there
    are tokens to show.
--}}
<tr class="empty">
    <td class="empty__cell" colspan="{{ $colspan }}">
        <h2 class="empty__title">{{ $title }}</h2>

        @if ($body)
            <p class="empty__body">{{ $body }}</p>
        @endif

        @if ($filters)
            <ul class="empty__tokens">
                @foreach ($filters as $filter)
                    <li class="empty__token">
                        <span class="empty__token-label">{{ $filter['label'] }}</span>
                        <a class="empty__remove"
                            href="{{ $filter['removeHref'] }}"
                            aria-label="{{ $filter['removeLabel'] }}">
                            <svg viewBox="0 0 10 10" width="10" height="10" aria-hidden="true" focusable="false"><path d="M1.6 1 5 4.4 8.4 1l.6.6L5.6 5 9 8.4l-.6.6L5 5.6 1.6 9 1 8.4 4.4 5 1 1.6Z" fill="currentColor"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="empty__actions">
                <a class="empty__clear" href="{{ $clear }}">Clear all filters</a>
            </p>
        @endif
    </td>
</tr>
