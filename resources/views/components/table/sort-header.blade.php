@props(['column', 'label', 'state', 'align' => 'start'])
{{--
    A sortable column header.

    This component exists for one reason: aria-sort belongs on the <th>, not on
    the link inside it, and it must be OMITTED on inactive columns rather than
    set to "none" -- aria-sort="none" on four headers tells a screen-reader user
    that four columns are sorted. Getting that right in five places by hand is
    how it ends up wrong in one.

    The caret is drawn from the attribute, not from a modifier class, so the
    arrow and the announcement cannot disagree.
--}}
<th scope="col"
    {{ $attributes->merge(['class' => 'orders__th' . ($align === 'end' ? ' is-num' : '')]) }}
    @if ($state['column'] === $column) aria-sort="{{ $state['dir'] === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <a class="orders__sort" href="{{ orders_sort_url($state, $column) }}">
        {{ $label }}<span class="orders__caret" aria-hidden="true"></span>
    </a>
</th>
