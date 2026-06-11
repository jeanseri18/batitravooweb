{{-- Activité métier — aligné sur les menus profil / accueil Flutter par rôle --}}
@php
    $slug = $profileSlug ?? '';
    $p = $page ?? '';
    $cv = $candidatureVue ?? 'recues';
@endphp

@if ($slug === 'particulier')
    <p class="app-sidebar__section-kicker app-mt-md">Mes projets</p>
    <nav class="app-nav app-nav--profile-menu" aria-label="Commandes et devis">
        <a href="{{ route('app.particulier.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'document'])<span>Gestion des devis</span></a>
    </nav>

@elseif ($slug === 'artisan')
    <p class="app-sidebar__section-kicker app-mt-md">Mon activité</p>
    <nav class="app-nav app-nav--profile-menu" aria-label="Activité artisan">
        <a href="{{ route('app.artisan.services') }}" class="{{ in_array($p, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span>Mes services</span></a>
        <a href="{{ route('app.artisan.business_card') }}" class="{{ $p === 'artisan_carte_visite' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'briefcase'])<span>Ma carte de visite</span></a>
        <a href="{{ route('app.artisan.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'document'])<span>Gestion des devis</span></a>
    </nav>

@elseif ($slug === 'batiment')
    <p class="app-sidebar__section-kicker app-mt-md">Gestion</p>
    <nav class="app-nav app-nav--profile-menu" aria-label="Gestion entreprise BTP">
        <a href="{{ route('app.batiment.besoins') }}" class="{{ in_array($p, ['besoins_manage', 'besoin_create', 'besoin_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'pin'])<span>Besoins</span></a>
        <a href="{{ route('app.batiment.services') }}" class="{{ in_array($p, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span>Services</span></a>
        <a href="{{ route('app.batiment.candidatures', ['vue' => 'recues']) }}" class="{{ $p === 'candidatures' && $cv === 'recues' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span>Candidatures reçues</span></a>
        <a href="{{ route('app.batiment.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'document'])<span>Gestion des devis</span></a>
    </nav>

@elseif ($slug === 'fournisseur')
    <nav class="app-nav app-nav--profile-menu app-mt-md" aria-label="Catalogue et commandes">
        <a href="{{ route('app.fournisseur.products') }}" class="{{ in_array($p, ['products_manage', 'product_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'cube'])<span>Catalogue produits</span></a>
        <a href="{{ route('app.fournisseur.commandes') }}" class="{{ $p === 'fournisseur_orders' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'stack'])<span>Mes commandes</span></a>
        <a href="{{ route('app.fournisseur.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'document'])<span>Mes devis</span></a>
    </nav>
@endif
