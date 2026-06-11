@php
    $profileSlug = $profileSlug ?? '';
@endphp

@if ($profileSlug === 'particulier')
    <section class="app-home-block app-home-block--shortcuts" aria-labelledby="home-shortcuts-title">
        @include('app.shell.partials.home_section_head', [
            'title' => 'Accès rapide',
            'scrollTarget' => '#home-shortcuts-carousel',
            'sectionId' => 'home-shortcuts-title',
        ])
        <div class="app-card app-home-shortcuts app-home-section">
        @php
            $devisDir = request('direction', 'received');
            $devisKind = request('kind', '');
            $actCmd = str_starts_with($page, 'devis') && $devisDir === 'sent' && ($devisKind === '' || $devisKind === 'catalog');
            $actDevis = str_starts_with($page, 'devis') && $devisDir === 'sent' && $devisKind === 'marketplace';
            $actDevisRec = str_starts_with($page, 'devis') && $devisDir === 'received';
        @endphp
        <div class="app-home-shortcuts__grid app-home-carousel" id="home-shortcuts-carousel">
            <a href="{{ route('app.particulier.devis', ['direction' => 'sent', 'kind' => 'catalog']) }}" class="app-workflow-tile app-home-shortcut {{ $actCmd ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'cart'])<span class="app-workflow-tile__title">Mes commandes</span></span>
                <span class="app-workflow-tile__hint">Achats matériaux</span>
            </a>
            <a href="{{ route('app.particulier.devis', ['direction' => 'sent', 'kind' => 'marketplace']) }}" class="app-workflow-tile app-home-shortcut {{ $actDevis ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'document'])<span class="app-workflow-tile__title">Mes devis</span></span>
                <span class="app-workflow-tile__hint">Demandes prestataires</span>
            </a>
            <a href="{{ route('app.particulier.devis', ['direction' => 'received']) }}" class="app-workflow-tile app-home-shortcut {{ $actDevisRec ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span class="app-workflow-tile__title">Devis reçus</span></span>
                <span class="app-workflow-tile__hint">Réponses prestataires</span>
            </a>
            <a href="{{ route('app.particulier.marketplace') }}" class="app-workflow-tile app-home-shortcut {{ $page === 'marketplace' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'rocket'])<span class="app-workflow-tile__title">Marketplace</span></span>
                <span class="app-workflow-tile__hint">Prestataires &amp; produits</span>
            </a>
        </div>
        </div>
    </section>
@elseif ($profileSlug === 'artisan')
    @php
        $tab = request('tab', 'besoins');
        $actOpp = $page === 'marketplace' && $tab === 'besoins';
        $actMissions = str_starts_with($page, 'devis') && request('direction', 'received') !== 'sent';
        $actCandidatures = $page === 'candidatures' && ($candidatureVue ?? 'envoyees') === 'envoyees';
    @endphp
    <section class="app-home-block app-home-block--shortcuts" aria-labelledby="home-shortcuts-title">
        @include('app.shell.partials.home_section_head', [
            'title' => 'Accès rapide',
            'scrollTarget' => '#home-shortcuts-carousel',
            'sectionId' => 'home-shortcuts-title',
        ])
        <div class="app-card app-home-shortcuts app-home-section">
        <div class="app-home-shortcuts__grid app-home-carousel" id="home-shortcuts-carousel">
            <a href="{{ route('app.artisan.business_card') }}" class="app-workflow-tile app-home-shortcut {{ $page === 'artisan_carte_visite' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'briefcase'])<span class="app-workflow-tile__title">Ma carte de visite</span></span>
                <span class="app-workflow-tile__hint">Profil public</span>
            </a>
            <a href="{{ route('app.artisan.services') }}" class="app-workflow-tile app-home-shortcut {{ in_array($page, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span class="app-workflow-tile__title">Mes services</span></span>
                <span class="app-workflow-tile__hint">Annonces &amp; tarifs</span>
            </a>
            <a href="{{ route('app.artisan.devis', ['direction' => 'received']) }}" class="app-workflow-tile app-home-shortcut {{ $actMissions ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span class="app-workflow-tile__title">Mes missions</span></span>
                <span class="app-workflow-tile__hint">En cours, gagnées, perdues</span>
            </a>
            <a href="{{ route('app.artisan.candidatures', ['vue' => 'envoyees']) }}" class="app-workflow-tile app-home-shortcut {{ $actCandidatures ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'send'])<span class="app-workflow-tile__title">Candidatures envoyées</span></span>
                <span class="app-workflow-tile__hint">Réponses aux opportunités</span>
            </a>
            <a href="{{ route('app.artisan.marketplace', ['tab' => 'besoins']) }}" class="app-workflow-tile app-home-shortcut {{ $actOpp ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'rocket'])<span class="app-workflow-tile__title">Opportunités</span></span>
                <span class="app-workflow-tile__hint">Besoins BTP</span>
            </a>
        </div>
        </div>
    </section>
@elseif ($profileSlug === 'batiment')
    <section class="app-home-block app-home-block--shortcuts" aria-labelledby="home-shortcuts-title">
        @include('app.shell.partials.home_section_head', [
            'title' => 'Accès rapide',
            'scrollTarget' => '#home-shortcuts-carousel',
            'sectionId' => 'home-shortcuts-title',
        ])
        <div class="app-card app-home-shortcuts app-home-section">
        <div class="app-home-shortcuts__grid app-home-carousel" id="home-shortcuts-carousel">
            <a href="{{ route('app.batiment.chantiers') }}" class="app-workflow-tile app-home-shortcut {{ $page === 'chantiers' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'building'])<span class="app-workflow-tile__title">Mes chantiers</span></span>
                <span class="app-workflow-tile__hint">Chantiers</span>
            </a>
            <a href="{{ route('app.batiment.services') }}" class="app-workflow-tile app-home-shortcut {{ in_array($page, ['services_manage', 'service_form'], true) ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'wrench'])<span class="app-workflow-tile__title">Gestion des services</span></span>
                <span class="app-workflow-tile__hint">Prestations</span>
            </a>
            <a href="{{ route('app.batiment.candidatures', ['vue' => 'recues']) }}" class="app-workflow-tile app-home-shortcut {{ $page === 'candidatures' && ($candidatureVue ?? 'recues') === 'recues' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'inbox'])<span class="app-workflow-tile__title">Gestion des candidatures</span></span>
                <span class="app-workflow-tile__hint">Réponses artisans</span>
            </a>
            <a href="{{ route('app.batiment.devis') }}" class="app-workflow-tile app-home-shortcut {{ str_starts_with($page, 'devis') ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'document'])<span class="app-workflow-tile__title">Gestion des devis</span></span>
                <span class="app-workflow-tile__hint">Reçus &amp; envoyés</span>
            </a>
        </div>
        </div>
    </section>
@elseif ($profileSlug === 'fournisseur')
    <section class="app-home-block app-home-block--shortcuts" aria-labelledby="home-shortcuts-title">
        @include('app.shell.partials.home_section_head', [
            'title' => 'Accès rapide',
            'scrollTarget' => '#home-shortcuts-carousel',
            'sectionId' => 'home-shortcuts-title',
        ])
        <div class="app-card app-home-shortcuts app-home-section">
        <div class="app-home-shortcuts__grid app-home-carousel" id="home-shortcuts-carousel">
            <a href="{{ route('app.fournisseur.products') }}" class="app-workflow-tile app-home-shortcut {{ in_array($page, ['products_manage', 'product_form'], true) ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'cube'])<span class="app-workflow-tile__title">Catalogue produits</span></span>
                <span class="app-workflow-tile__hint">Inventaire</span>
            </a>
            <a href="{{ route('app.fournisseur.devis') }}" class="app-workflow-tile app-home-shortcut {{ str_starts_with($page, 'devis') ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'document'])<span class="app-workflow-tile__title">Mes devis</span></span>
                <span class="app-workflow-tile__hint">Demandes &amp; propositions</span>
            </a>
            <a href="{{ route('app.fournisseur.commandes') }}" class="app-workflow-tile app-home-shortcut {{ $page === 'fournisseur_orders' ? 'is-active' : '' }}">
                <span class="app-workflow-tile__row">@include('app.partials.app-nav-icon', ['name' => 'stack'])<span class="app-workflow-tile__title">Mes commandes</span></span>
                <span class="app-workflow-tile__hint">Catalogue clients</span>
            </a>
        </div>
        </div>
    </section>
@endif
