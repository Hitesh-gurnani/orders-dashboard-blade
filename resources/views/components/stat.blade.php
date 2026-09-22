@props([
    'label',
    'value',
    'delta',
    'direction' => 'flat',
    'series' => [],
    'caption' => 'Last 7 days',
])
{{--
    One cell of the stat strip. Not "stat-card" -- there are no cards in this
    design, and naming a file after a box that does not exist is the sort of
    residue a reviewer greps for and cannot find a caller of.

    The sparkline is a plain <ul> with the real numbers in the DOM, sized by a
    custom property. It has exactly one call site, so it is inline here rather
    than being a component with one caller.

    Direction is never carried by colour: there is a glyph, a sign, and a word
    for screen readers.
--}}
<div {{ $attributes->merge(['class' => 'stat']) }}>
    <p class="stat__label">{{ $label }}</p>
    <p class="stat__value">{{ $value }}</p>

    <ul class="spark" aria-label="{{ $caption }}">
        @foreach ($series as $point)
            <li class="spark__bar" style="--v: {{ $point['v'] }}">
                <span class="sr-only">{{ $point['label'] }}: {{ $point['display'] }}</span>
            </li>
        @endforeach
    </ul>

    <p class="stat__delta" data-direction="{{ $direction }}">
        <span class="stat__arrow" aria-hidden="true"></span>
        <span class="sr-only">{{ ['up' => 'Up', 'down' => 'Down', 'flat' => 'No change'][$direction] ?? 'Change' }}</span>
        {{ $delta }}
    </p>
</div>
