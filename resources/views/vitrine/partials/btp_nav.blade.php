@php
    $annuaireActive = request()->routeIs('vitrine.annuaire');
    $homeUrl = url('/');
@endphp

<header class="site-header">
    <nav class="nav-inner" aria-label="Navigation principale">
        <a href="{{ $homeUrl }}" class="brand">
            <img src="{{ asset('images/logo.png') }}" alt="BATITRAVOO" width="160" height="48">
        </a>
        <ul class="nav-links">
            <li><a href="{{ $homeUrl }}#accueil">Accueil</a></li>
            <li><a href="{{ route('vitrine.annuaire') }}" @class(['is-active' => $annuaireActive])>Annuaire</a></li>
            <li><a href="{{ $homeUrl }}#solution">Solution</a></li>
            <li><a href="{{ $homeUrl }}#pour-qui">Métiers</a></li>
            <li><a href="{{ $homeUrl }}#fonctionnalites">Services</a></li>
            <li><a href="{{ $homeUrl }}#temoignages">Références</a></li>
        </ul>
        <div class="nav-actions">
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="btn-nav-ghost">Connexion</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-nav">Créer un compte</a>
            @else
                <a href="{{ $homeUrl }}#inscription" class="btn-nav">Créer un compte</a>
            @endif
            <button type="button" class="nav-toggle" aria-label="Menu" aria-expanded="false" aria-controls="nav-drawer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </nav>
</header>

<div class="nav-drawer" id="nav-drawer" aria-hidden="true">
    <div class="nav-drawer__panel">
        <ul class="nav-drawer__links">
            <li><a href="{{ $homeUrl }}#accueil">Accueil</a></li>
            <li><a href="{{ route('vitrine.annuaire') }}" @class(['is-active' => $annuaireActive])>Annuaire</a></li>
            <li><a href="{{ $homeUrl }}#solution">Solution</a></li>
            <li><a href="{{ $homeUrl }}#pour-qui">Métiers</a></li>
            <li><a href="{{ $homeUrl }}#fonctionnalites">Services</a></li>
            <li><a href="{{ $homeUrl }}#temoignages">Références</a></li>
            @if (Route::has('login'))
                <li><a href="{{ route('login') }}">Connexion</a></li>
            @endif
            @if (Route::has('register'))
                <li><a href="{{ route('register') }}">Créer un compte</a></li>
            @endif
        </ul>
    </div>
</div>
