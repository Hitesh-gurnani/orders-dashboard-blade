@props([
    'title',
    'theme' => 'auto',
    'density' => 'cozy',
    'nav' => [],
    'current' => 'orders',
    'period' => '1–22 Sep 2026',
    'query' => [],
])
{{--
    The document shell. One caller by definition -- it is a layout, not an
    abstraction, and it does not pretend otherwise.

    The topbar and sidebar are data-driven rather than named slots: a named slot
    for each would be a page fragment wearing a costume, and the page template
    would end up owning markup it has no reason to know about.

    data-theme is server-rendered from a cookie, so there is no blocking inline
    script and no flash of the wrong theme on any navigation.
--}}
<!DOCTYPE html>
<html lang="en"
    @if ($theme !== 'auto') data-theme="{{ $theme }}" @endif
    data-density="{{ $density }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    {{-- Inline, so no request is made for /favicon.ico -- which, under
         `php -S`, would otherwise render this entire page to answer it. --}}
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Crect width='16' height='16' rx='3' fill='%2314624a'/%3E%3Crect x='3' y='4.5' width='10' height='1.6' fill='white'/%3E%3Crect x='3' y='7.2' width='10' height='1.6' fill='white' opacity='.7'/%3E%3Crect x='3' y='9.9' width='6' height='1.6' fill='white' opacity='.45'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="/css/tokens.css">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <a class="skip" href="#orders">Skip to the orders table</a>

    <div class="shell">
        <header class="topbar">
            <h1 class="topbar__title">{{ $title }}</h1>

            {{-- Static text, not a control. A date picker that filtered nothing
                 would be the only lie on the page. --}}
            <p class="topbar__period">{{ $period }}</p>

            <div class="switch" role="group" aria-label="Colour theme">
                @foreach (['auto' => 'Auto', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                    <a class="switch__opt"
                        href="{{ orders_url(['theme' => $value] + $query) }}"
                        @if ($theme === $value) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>

            <div class="switch" role="group" aria-label="Row density">
                @foreach (['cozy' => 'Cozy', 'compact' => 'Compact'] as $value => $label)
                    <a class="switch__opt"
                        href="{{ orders_url(['density' => $value] + $query) }}"
                        @if ($density === $value) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </header>

        <nav class="nav" aria-label="Sections">
            <p class="nav__brand">Northwind<br><span>Supply Co.</span></p>

            <ul class="nav__list">
                @foreach ($nav as $item)
                    <li>
                        <x-nav-link
                            :href="$item['href']"
                            :label="$item['label']"
                            :current="$item['key'] === $current"
                            :count="$item['count'] ?? null" />
                    </li>
                @endforeach
            </ul>
        </nav>

        <main {{ $attributes->merge(['class' => 'main']) }}>
            {{ $slot }}
        </main>
    </div>
</body>
</html>
