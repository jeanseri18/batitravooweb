{{--
  Logo marque : PNG si présent (public/images/logo.png), sinon images/logo.svg,
  sinon SVG inline. Variante « inverse » pour fonds marine (auth hero, en-tête compléter profil).
  @param string|null $variant « inverse »
  @param string $size sidebar | header | sm | hero
--}}
@php
    $inverse = ($variant ?? '') === 'inverse';
    $sizeKey = $size ?? 'sidebar';
    $sizeClass = is_string($sizeKey) ? preg_replace('/[^a-z0-9_-]/', '', $sizeKey) : 'sidebar';
    if ($sizeClass === '') {
        $sizeClass = 'sidebar';
    }
    $alt = config('app.name', 'BATITRAVOO');
    $hasPng = file_exists(public_path('images/logo.png'));
    $hasSvg = file_exists(public_path('images/logo.svg'));
@endphp
@if ($inverse)
    @if ($hasPng)
        <span class="app-brand-logo-wrap app-brand-logo-wrap--on-dark">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="{{ $alt }}"
                width="200"
                height="80"
                decoding="async"
                loading="eager"
                class="app-brand-logo-img app-brand-logo-img--{{ $sizeClass }}"
            >
        </span>
    @else
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 260 52"
            class="app-brand-logo-svg app-brand-logo-svg--inverse app-brand-logo-svg--{{ $sizeClass }}"
            role="img"
            aria-label="{{ $alt }}"
        >
            <text x="2" y="38" font-family="system-ui, -apple-system, Segoe UI, sans-serif" font-size="32" font-weight="700">BATI</text>
            <text x="98" y="38" font-family="system-ui, -apple-system, Segoe UI, sans-serif" font-size="32" font-weight="700">TRAV</text>
            <text x="196" y="38" font-family="system-ui, -apple-system, Segoe UI, sans-serif" font-size="32" font-weight="700">OO</text>
        </svg>
    @endif
@else
    @if ($hasPng)
        <img
            src="{{ asset('images/logo.png') }}"
            alt="{{ $alt }}"
            width="200"
            height="80"
            decoding="async"
            loading="eager"
            class="app-brand-logo-img app-brand-logo-img--{{ $sizeClass }}"
        >
    @elseif ($hasSvg)
        <img
            src="{{ asset('images/logo.svg') }}"
            alt="{{ $alt }}"
            width="200"
            height="40"
            decoding="async"
            loading="eager"
            class="app-brand-logo-img app-brand-logo-img--{{ $sizeClass }}"
        >
    @else
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 260 52"
            class="app-brand-logo-svg app-brand-logo-svg--{{ $sizeClass }}"
            role="img"
            aria-label="{{ $alt }}"
        >
            <text x="2" y="38" font-family="system-ui, -apple-system, Segoe UI, sans-serif" font-size="32" font-weight="700">BATI</text>
            <text x="98" y="38" font-family="system-ui, -apple-system, Segoe UI, sans-serif" font-size="32" font-weight="700">TRAV</text>
            <text x="196" y="38" font-family="system-ui, -apple-system, Segoe UI, sans-serif" font-size="32" font-weight="700">OO</text>
        </svg>
    @endif
@endif
