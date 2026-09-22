@props(['href', 'label', 'current' => false, 'count' => null])
{{--
    House rule, stated once here and held everywhere: CLASSES CARRY VARIANT,
    ATTRIBUTES CARRY STATE. There is no .is-active and no .nav__link--current --
    the current item is styled through [aria-current="page"], which is the same
    attribute that announces it. A styling bug and an accessibility bug cannot
    drift apart if they are the same bug.
--}}
<a {{ $attributes->merge(['class' => 'nav__link']) }}
    href="{{ $href }}"
    @if ($current) aria-current="page" @endif>
    <span class="nav__label">{{ $label }}</span>
    @if ($count !== null)
        <span class="nav__count">{{ $count }}</span>
    @endif
</a>
