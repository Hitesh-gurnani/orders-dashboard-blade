@props(['order'])
{{--
    One order. Twenty callers per page, which is exactly why it is a component:
    these are the decisions that must be identical on every row.

    The rail is a custom property written onto the <tr> and consumed in CSS as
    an inset shadow on the first cell -- no extra DOM, no per-status selector,
    and it simply does not paint where it is not set. Only exception rows carry
    one; see OrderStatus::isException().
--}}
<tr class="orders__row"
    @if ($order['status']->isException()) style="--rail: var(--rail-{{ $order['status']->value }})" @endif>
    <td class="orders__cell orders__cell--id">
        {{-- There is no order detail view, so this filters to the one order
             rather than pointing at a route that would 404. Every link on this
             page goes somewhere real. --}}
        <a class="orders__link" href="{{ $order['href'] }}">{{ $order['number'] }}</a>
    </td>

    <td class="orders__cell orders__cell--customer">
        <span class="orders__truncate" title="{{ $order['customer'] }}">{{ $order['customer'] }}</span>
    </td>

    <td class="orders__cell col-placed">
        <time datetime="{{ $order['placed_iso'] }}" title="{{ $order['placed_title'] }}">{{ $order['placed_display'] }}</time>
    </td>

    <td class="orders__cell is-num col-items">{{ $order['items'] }}</td>

    <td class="orders__cell is-num">
        {{-- No conditional class here: a cancelled total is dimmed in CSS via
             the badge already in this row. The state is in the markup, so the
             template does not need to restate it. --}}
        <x-money :value="$order['total']" />
    </td>

    <td class="orders__cell orders__cell--status">
        <x-badge :status="$order['status']" />
    </td>
</tr>
