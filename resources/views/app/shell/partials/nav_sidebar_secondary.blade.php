{{-- Raccourcis métier (équivalent tuiles / écrans poussés sur mobile). --}}
@php
    $slug = $profileSlug ?? '';
    $p = $page ?? '';
    $cv = $candidatureVue ?? 'recues';
@endphp
<p class="app-sidebar__section-kicker app-mt-md">Activité</p>
<nav class="app-nav app-nav--profile-menu" aria-label="Raccourcis métier">
@if ($slug === 'particulier')
    <a href="{{ route('app.particulier.marketplace', ['tab' => 'services', 'service_kind' => 'entrepreneur']) }}" class="{{ $p === 'marketplace' && request('tab', 'produits') === 'services' && request('service_kind') === 'entrepreneur' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'search'])<span>Prestataires BTP</span></a>
    <a href="{{ route('app.particulier.marketplace', ['tab' => 'produits']) }}" class="{{ $p === 'marketplace' && request('tab') === 'produits' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span>Matériaux</span></a>
    <a href="{{ route('app.particulier.marketplace', ['tab' => 'services', 'service_kind' => 'artisan']) }}" class="{{ $p === 'marketplace' && request('tab') === 'services' && request('service_kind') === 'artisan' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span>Artisans</span></a>
    <a href="{{ route('app.particulier.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'document'])<span>Mes demandes de devis</span></a>
    <a href="{{ route('app.particulier.besoins') }}" class="{{ in_array($p, ['besoins_manage', 'besoin_create', 'besoin_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'pin'])<span>Mes besoins</span></a>
    <a href="{{ route('app.particulier.candidatures') }}" class="{{ $p === 'candidatures' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span>Candidatures</span></a>
    <a href="{{ route('app.particulier.documents') }}" class="{{ $p === 'documents' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'paperclip'])<span>Documents</span></a>
    <a href="{{ route('app.particulier.cart') }}" class="{{ $p === 'supplier_cart' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span>Panier</span></a>
    <a href="{{ route('app.particulier.service_client') }}" class="{{ $p === 'service_client' || str_starts_with($p, 'support') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'support'])<span>Service client</span></a>
    <a href="{{ route('app.particulier.profile.password.page') }}" class="{{ $p === 'profile_password' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'lock'])<span>Mot de passe</span></a>
    <a href="{{ route('app.particulier.help') }}" class="{{ $p === 'help_particulier' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'help'])<span>Centre d’aide</span></a>
@elseif ($slug === 'artisan')
    <a href="{{ route('app.artisan.services') }}" class="{{ in_array($p, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span>Mes services</span></a>
    <a href="{{ route('app.artisan.business_card') }}" class="{{ $p === 'artisan_carte_visite' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'briefcase'])<span>Ma carte de visite</span></a>
    <a href="{{ route('app.artisan.marketplace', ['tab' => 'besoins']) }}" class="{{ $p === 'marketplace' && request('tab', 'besoins') === 'besoins' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'rocket'])<span>Opportunités (besoins)</span></a>
    <a href="{{ route('app.artisan.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span>Missions reçues</span></a>
    <a href="{{ route('app.artisan.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'document'])<span>Mes devis</span></a>
    <a href="{{ route('app.artisan.documents') }}" class="{{ $p === 'documents' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'paperclip'])<span>Documents</span></a>
    <a href="{{ route('app.artisan.support') }}" class="{{ str_starts_with($p, 'support') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'support'])<span>Support</span></a>
    <a href="{{ route('app.artisan.profile.password.page') }}" class="{{ $p === 'profile_password' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'lock'])<span>Mot de passe</span></a>
    <a href="{{ route('app.artisan.help') }}" class="{{ $p === 'help_artisan' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'help'])<span>Centre d’aide</span></a>
@elseif ($slug === 'batiment')
    <a href="{{ route('app.batiment.besoins') }}" class="{{ in_array($p, ['besoins_manage', 'besoin_create', 'besoin_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'pin'])<span>Gestion des besoins</span></a>
    <a href="{{ route('app.batiment.services') }}" class="{{ in_array($p, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span>Gestion des services</span></a>
    <a href="{{ route('app.batiment.candidatures', ['vue' => 'recues']) }}" class="{{ $p === 'candidatures' && $cv === 'recues' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span>Candidatures reçues</span></a>
    <a href="{{ route('app.batiment.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'document'])<span>Devis</span></a>
    <a href="{{ route('app.batiment.public_preview') }}" class="{{ $p === 'vue_publique_batiment' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'search'])<span>Vue publique</span></a>
    <a href="{{ route('app.batiment.documents') }}" class="{{ $p === 'documents' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'paperclip'])<span>Documents</span></a>
    <a href="{{ route('app.batiment.cart') }}" class="{{ $p === 'supplier_cart' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span>Panier</span></a>
    <a href="{{ route('app.batiment.support') }}" class="{{ str_starts_with($p, 'support') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'support'])<span>Support</span></a>
    <a href="{{ route('app.batiment.profile.location.page') }}" class="{{ $p === 'profile_location' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'globe'])<span>Localisation</span></a>
    <a href="{{ route('app.batiment.profile.password.page') }}" class="{{ $p === 'profile_password' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'lock'])<span>Mot de passe</span></a>
    <a href="{{ route('app.batiment.help') }}" class="{{ $p === 'help_batiment' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'help'])<span>Centre d’aide</span></a>
@elseif ($slug === 'fournisseur')
    <a href="{{ route('app.fournisseur.cart') }}" class="{{ $p === 'supplier_cart' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span>Panier</span></a>
    <a href="{{ route('app.fournisseur.products') }}" class="{{ in_array($p, ['products_manage', 'product_form'], true) ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'cube'])<span>Catalogue produits</span></a>
    <a href="{{ route('app.fournisseur.devis') }}" class="{{ str_starts_with($p, 'devis') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'stack'])<span>Commandes &amp; devis</span></a>
    <a href="{{ route('app.fournisseur.candidatures') }}" class="{{ $p === 'candidatures' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span>Candidatures</span></a>
    <a href="{{ route('app.fournisseur.public_preview') }}" class="{{ $p === 'vue_publique' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'search'])<span>Vue publique</span></a>
    <a href="{{ route('app.fournisseur.documents') }}" class="{{ $p === 'documents' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'paperclip'])<span>Documents</span></a>
    <a href="{{ route('app.fournisseur.support') }}" class="{{ str_starts_with($p, 'support') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'support'])<span>Support</span></a>
    <a href="{{ route('app.fournisseur.profile.location.page') }}" class="{{ $p === 'profile_location' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'globe'])<span>Localisation</span></a>
    <a href="{{ route('app.fournisseur.profile.password.page') }}" class="{{ $p === 'profile_password' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'lock'])<span>Mot de passe</span></a>
    <a href="{{ route('app.fournisseur.help') }}" class="{{ $p === 'help_fournisseur' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'help'])<span>Centre d’aide</span></a>
@endif
</nav>
