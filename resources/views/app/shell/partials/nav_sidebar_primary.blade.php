{{-- Navigation principale — Paramètres via icône topbar uniquement --}}
@php
    $slug = $profileSlug ?? '';
    $mpEntry = match ($slug) {
        'particulier' => route('app.particulier.marketplace', ['tab' => 'services', 'service_kind' => 'entrepreneur']),
        'artisan' => route('app.artisan.marketplace', ['tab' => 'besoins']),
        'batiment' => route('app.batiment.marketplace', ['tab' => 'produits']),
        'fournisseur' => route('app.fournisseur.marketplace', ['tab' => 'produits']),
        default => route('app.particulier.home'),
    };
    $p = $page ?? '';
    $homeActive = $p === 'home';
    $dashActive = $p === 'dashboard_tab';
    $mpActive = $p === 'marketplace';
    $msgActive = $p === 'messages';
@endphp
<nav class="app-nav app-nav--primary app-nav--profile-menu" aria-label="Navigation principale">
    <a href="{{ route('app.'.$slug.'.home') }}" class="{{ $homeActive ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'home'])<span>Accueil</span></a>
    <a href="{{ route('app.'.$slug.'.dashboard') }}" class="{{ $dashActive ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'chart'])<span>Tableau de bord</span></a>
    <a href="{{ $mpEntry }}" class="{{ $mpActive ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'clipboard'])<span>Marketplace</span></a>
    <a href="{{ route('app.'.$slug.'.messages') }}" class="{{ $msgActive ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'chat'])<span>Messages</span></a>
</nav>
