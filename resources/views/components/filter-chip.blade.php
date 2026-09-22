@props(['href', 'label', 'count', 'current' => false])
{{--
    A link, not a button. Filtering changes which orders you are looking at, and
    that is a different address -- so it is navigation, it works with JavaScript
    disabled, and it is shareable.
--}}
<a {{ $attributes->merge(['class' => 'chip']) }}
    href="{{ $href }}"
    @if ($current) aria-current="page" @endif>
    {{ $label }}<span class="chip__count">{{ $count }}</span>
</a>
