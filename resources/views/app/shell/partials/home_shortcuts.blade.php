{{-- Grilles d’accueil alignées sur Flutter (tuiles principales), sans dupliquer le dock latéral. --}}
@if ($profileSlug === 'particulier')
    @php
        $tab = request('tab', 'produits');
        $sk = request('service_kind');
        $actMpBtp = $page === 'marketplace' && $tab === 'services' && $sk === 'entrepreneur';
        $actMpMat = $page === 'marketplace' && $tab === 'produits';
        $actMpArt = $page === 'marketplace' && $tab === 'services' && $sk === 'artisan';
    @endphp
    <section class="app-card app-mt app-shortcuts" aria-labelledby="home-shortcuts-title">
        <h2 id="home-shortcuts-title" class="app-section-title app-section-title--flush app-mb-sm">Accès rapide</h2>
        <div class="app-workflow-grid">
            <a href="{{ route('app.particulier.marketplace', ['tab' => 'services', 'service_kind' => 'entrepreneur']) }}" class="app-workflow-tile {{ $actMpBtp ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'search'])<span class="app-workflow-tile__title">Trouver un prestataire BTP</span></span>
                <span class="app-workflow-tile__hint">Marketplace</span>
            </a>
            <a href="{{ route('app.particulier.marketplace', ['tab' => 'produits']) }}" class="app-workflow-tile {{ $actMpMat ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span class="app-workflow-tile__title">Acheter des matériaux</span></span>
                <span class="app-workflow-tile__hint">Fournisseurs</span>
            </a>
            <a href="{{ route('app.particulier.marketplace', ['tab' => 'services', 'service_kind' => 'artisan']) }}" class="app-workflow-tile {{ $actMpArt ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span class="app-workflow-tile__title">Trouver un artisan</span></span>
                <span class="app-workflow-tile__hint">Corps de métier</span>
            </a>
            <a href="{{ route('app.particulier.cart') }}" class="app-workflow-tile {{ $page === 'supplier_cart' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span class="app-workflow-tile__title">Panier</span></span>
                <span class="app-workflow-tile__hint">Commandes catalogue</span>
            </a>
            <a href="{{ route('app.particulier.service_client') }}" class="app-workflow-tile {{ $page === 'service_client' || str_starts_with($page, 'support') ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'support'])<span class="app-workflow-tile__title">Service client</span></span>
                <span class="app-workflow-tile__hint">Support &amp; aide</span>
            </a>
        </div>
    </section>
@elseif ($profileSlug === 'artisan')
    @php
        $tab = request('tab', 'besoins');
        $actOpp = $page === 'marketplace' && $tab === 'besoins';
    @endphp
    <section class="app-card app-mt app-shortcuts" aria-labelledby="home-shortcuts-title">
        <h2 id="home-shortcuts-title" class="app-section-title app-section-title--flush app-mb-sm">Accès rapide</h2>
        <div class="app-workflow-grid">
            <a href="{{ route('app.artisan.services') }}" class="app-workflow-tile {{ in_array($page, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span class="app-workflow-tile__title">Mes services</span></span>
                <span class="app-workflow-tile__hint">Annonces &amp; tarifs</span>
            </a>
            <a href="{{ route('app.artisan.business_card') }}" class="app-workflow-tile {{ $page === 'artisan_carte_visite' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'briefcase'])<span class="app-workflow-tile__title">Ma carte de visite</span></span>
                <span class="app-workflow-tile__hint">Profil public</span>
            </a>
            <a href="{{ route('app.artisan.marketplace', ['tab' => 'besoins']) }}" class="app-workflow-tile {{ $actOpp ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'rocket'])<span class="app-workflow-tile__title">Opportunités</span></span>
                <span class="app-workflow-tile__hint">Besoins BTP</span>
            </a>
            <a href="{{ route('app.artisan.devis') }}" class="app-workflow-tile {{ str_starts_with($page, 'devis') ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span class="app-workflow-tile__title">Missions reçues</span></span>
                <span class="app-workflow-tile__hint">Demandes de devis</span>
            </a>
            <a href="{{ route('app.artisan.devis') }}" class="app-workflow-tile {{ str_starts_with($page, 'devis') ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'document'])<span class="app-workflow-tile__title">Mes devis</span></span>
                <span class="app-workflow-tile__hint">Suivi</span>
            </a>
        </div>
    </section>
@elseif ($profileSlug === 'batiment')
    <section class="app-card app-mt app-shortcuts" aria-labelledby="home-shortcuts-title">
        <h2 id="home-shortcuts-title" class="app-section-title app-section-title--flush app-mb-sm">Accès rapide</h2>
        <div class="app-workflow-grid">
            <a href="{{ route('app.batiment.besoins') }}" class="app-workflow-tile {{ in_array($page, ['besoins_manage', 'besoin_create', 'besoin_form'], true) ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'pin'])<span class="app-workflow-tile__title">Gestion des besoins</span></span>
                <span class="app-workflow-tile__hint">Chantiers</span>
            </a>
            <a href="{{ route('app.batiment.services') }}" class="app-workflow-tile {{ in_array($page, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span class="app-workflow-tile__title">Gestion des services</span></span>
                <span class="app-workflow-tile__hint">Prestations</span>
            </a>
            <a href="{{ route('app.batiment.candidatures', ['vue' => 'recues']) }}" class="app-workflow-tile {{ $page === 'candidatures' && ($candidatureVue ?? 'recues') === 'recues' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span class="app-workflow-tile__title">Candidatures reçues</span></span>
                <span class="app-workflow-tile__hint">Réponses artisans</span>
            </a>
            <a href="{{ route('app.batiment.devis') }}" class="app-workflow-tile {{ str_starts_with($page, 'devis') ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'document'])<span class="app-workflow-tile__title">Gestion des devis</span></span>
                <span class="app-workflow-tile__hint">Envoi &amp; suivi</span>
            </a>
            <a href="{{ route('app.batiment.cart') }}" class="app-workflow-tile {{ $page === 'supplier_cart' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span class="app-workflow-tile__title">Panier</span></span>
                <span class="app-workflow-tile__hint">Matériaux marketplace</span>
            </a>
        </div>
    </section>
@elseif ($profileSlug === 'fournisseur')
    <section class="app-card app-mt app-shortcuts" aria-labelledby="home-shortcuts-title">
        <h2 id="home-shortcuts-title" class="app-section-title app-section-title--flush app-mb-sm">Accès rapide</h2>
        <div class="app-workflow-grid">
            <a href="{{ route('app.fournisseur.products') }}" class="app-workflow-tile {{ in_array($page, ['products_manage', 'product_form'], true) ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'cube'])<span class="app-workflow-tile__title">Catalogue produits</span></span>
                <span class="app-workflow-tile__hint">Inventaire</span>
            </a>
            <a href="{{ route('app.fournisseur.products.create') }}" class="app-workflow-tile {{ $page === 'product_form' && ($productFormMode ?? '') === 'create' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'plus'])<span class="app-workflow-tile__title">Ajouter un produit</span></span>
                <span class="app-workflow-tile__hint">Publication</span>
            </a>
            <a href="{{ route('app.fournisseur.devis') }}" class="app-workflow-tile {{ str_starts_with($page, 'devis') ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'stack'])<span class="app-workflow-tile__title">Gestion des commandes</span></span>
                <span class="app-workflow-tile__hint">Commandes &amp; devis</span>
            </a>
            <a href="{{ route('app.fournisseur.cart') }}" class="app-workflow-tile {{ $page === 'supplier_cart' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span class="app-workflow-tile__title">Panier</span></span>
                <span class="app-workflow-tile__hint">Alignement flux mobile</span>
            </a>
            <a href="{{ route('app.fournisseur.marketplace', ['tab' => 'produits']) }}" class="app-workflow-tile {{ $page === 'marketplace' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'clipboard'])<span class="app-workflow-tile__title">Marketplace</span></span>
                <span class="app-workflow-tile__hint">Découverte (comme mobile)</span>
            </a>
        </div>
    </section>
@endif
