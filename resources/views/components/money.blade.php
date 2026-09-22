@props(['value'])
{{--
    The dollars and the cents are separate spans so the cents can sit back a
    step without changing the number's alignment. The formatting itself is not
    here -- App\Support\Money does that once; this prints what it is handed.

    The minus is U+2212, not a hyphen: it shares the digits' width, so a
    negative total still lines up with the column above it.
--}}
<span {{ $attributes->merge(['class' => 'money']) }}>{{ $value->sign() }}<span class="money__int">{{ $value->int }}</span><span class="money__dec">{{ $value->dec }}</span></span>
