{{--
    Four elements, no conditional. Everything decided in App\View\Components\Badge,
    which is the entire argument for that class existing.

    Status is never carried by hue alone: the glyph is one shape family at four
    fill densities, and the chip border style differs per variant. Colour
    confirms; it does not inform.
--}}
<span {{ $attributes->merge(['class' => 'badge badge--' . $variant]) }}>
    <svg class="badge__glyph" viewBox="0 0 10 10" width="10" height="10" aria-hidden="true" focusable="false"><path d="{{ $glyphPath }}" fill="currentColor" fill-rule="evenodd"/></svg>
    <span class="badge__label">{{ $label }}</span>
    <span class="sr-only">, {{ $description }}</span>
</span>
