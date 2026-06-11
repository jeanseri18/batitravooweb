@php
    $slug = $profileSlug ?? '';
    $p = $page ?? '';
    $cv = $candidatureVue ?? 'recues';
    $unread = (int) ($unreadNotifications ?? 0);

    $mpEntry = match ($slug) {
        'particulier' => route('app.particulier.marketplace', ['tab' => 'services', 'service_kind' => 'entrepreneur']),
        'artisan' => route('app.artisan.marketplace', ['tab' => 'besoins']),
        'batiment' => route('app.batiment.marketplace', ['tab' => 'produits']),
        'fournisseur' => route('app.fournisseur.marketplace', ['tab' => 'produits']),
        default => route('app.particulier.home'),
    };

    $searchUrl = match ($slug) {
        'particulier' => route('app.particulier.marketplace'),
        'artisan' => route('app.artisan.marketplace'),
        'batiment' => route('app.batiment.marketplace'),
        'fournisseur' => route('app.fournisseur.marketplace'),
        default => route('app.'.$slug.'.marketplace'),
    };

    $searchPlaceholder = match ($slug) {
        'particulier' => 'Rechercher prestataires, matériaux…',
        'artisan' => 'Rechercher opportunités, besoins…',
        'batiment' => 'Rechercher produits, prestations…',
        'fournisseur' => 'Rechercher sur le marketplace…',
        default => 'Rechercher…',
    };

    $settingsNavPages = [
        'settings', 'profile', 'profile_password', 'profile_location', 'documents', 'notifications',
        'help_particulier', 'help_artisan', 'help_batiment', 'help_fournisseur',
        'vue_publique', 'vue_publique_batiment', 'service_client',
    ];
    $settingsNavActive = $p === 'settings'
        || in_array($p, $settingsNavPages, true)
        || str_starts_with($p, 'support');

    $authUser = auth()->user();
    $displayName = 'Membre';
    $initials = '?';
    $avatarUrl = null;
    $profileActive = false;

    if ($authUser) {
        $company = trim((string) ($authUser->company_name ?? ''));
        $useCompany = in_array($slug, ['batiment', 'fournisseur'], true) && $company !== '';
        $displayName = $useCompany ? $company : trim((string) ($authUser->name ?? 'Membre'));
        if ($displayName === '') {
            $displayName = 'Membre';
        }
        $initials = \Illuminate\Support\Str::of($displayName)->trim()->explode(' ')->filter()->take(2)
            ->map(fn (string $w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
        if ($initials === '') {
            $initials = '?';
        }
        $avatarUrl = $authUser->avatar_path ? storage_public_url($authUser->avatar_path) : null;
        $profileActive = in_array($p, ['profile', 'settings', 'profile_password', 'profile_location'], true);
    }

    $navLinks = [];

    if (in_array($slug, ['particulier', 'artisan', 'batiment', 'fournisseur'], true)) {
        $navLinks[] = ['href' => route('app.'.$slug.'.home'), 'label' => 'Accueil', 'active' => $p === 'home'];
        $navLinks[] = ['href' => route('app.'.$slug.'.dashboard'), 'label' => 'Tableau de bord', 'active' => $p === 'dashboard_tab'];
        $navLinks[] = ['href' => $mpEntry, 'label' => 'Marketplace', 'active' => $p === 'marketplace'];
        $navLinks[] = ['href' => route('app.'.$slug.'.messages'), 'label' => 'Messages', 'active' => $p === 'messages'];

        if ($slug === 'particulier') {
            $navLinks[] = ['href' => route('app.particulier.devis'), 'label' => 'Gestion des devis', 'active' => str_starts_with($p, 'devis')];
        } elseif ($slug === 'artisan') {
            $navLinks[] = ['href' => route('app.artisan.services'), 'label' => 'Mes services', 'active' => in_array($p, ['services_manage', 'service_form'], true)];
            $navLinks[] = ['href' => route('app.artisan.business_card'), 'label' => 'Ma carte de visite', 'active' => $p === 'artisan_carte_visite'];
            $navLinks[] = ['href' => route('app.artisan.devis'), 'label' => 'Gestion des devis', 'active' => str_starts_with($p, 'devis')];
        } elseif ($slug === 'batiment') {
            $navLinks[] = ['href' => route('app.batiment.besoins'), 'label' => 'Besoins', 'active' => in_array($p, ['besoins_manage', 'besoin_create', 'besoin_form'], true)];
            $navLinks[] = ['href' => route('app.batiment.services'), 'label' => 'Services', 'active' => in_array($p, ['services_manage', 'service_form'], true)];
            $navLinks[] = ['href' => route('app.batiment.candidatures', ['vue' => 'recues']), 'label' => 'Candidatures', 'active' => $p === 'candidatures' && $cv === 'recues'];
            $navLinks[] = ['href' => route('app.batiment.devis'), 'label' => 'Gestion des devis', 'active' => str_starts_with($p, 'devis')];
        } elseif ($slug === 'fournisseur') {
            $navLinks[] = ['href' => route('app.fournisseur.products'), 'label' => 'Catalogue produits', 'active' => in_array($p, ['products_manage', 'product_form'], true)];
            $navLinks[] = ['href' => route('app.fournisseur.commandes'), 'label' => 'Mes commandes', 'active' => $p === 'fournisseur_orders'];
            $navLinks[] = ['href' => route('app.fournisseur.devis'), 'label' => 'Mes devis', 'active' => str_starts_with($p, 'devis')];
        }
    }
@endphp

<header class="app-header" id="app-header">
    <div class="app-header__top">
        <a href="{{ route('app.'.$slug.'.home') }}" class="app-header__brand" aria-label="{{ config('app.name') }} — Accueil">
            @include('app.partials.brand-logo', ['size' => 'header', 'variant' => 'default'])
        </a>

        @if (in_array($slug, ['particulier', 'artisan', 'batiment', 'fournisseur'], true))
            <form method="get" action="{{ $searchUrl }}" class="app-header__search" role="search">
                @if ($slug === 'artisan')
                    <input type="hidden" name="tab" value="besoins">
                @elseif ($slug === 'particulier')
                    <input type="hidden" name="tab" value="services">
                @endif
                <label for="app-header-search" class="app-sr-only">Rechercher</label>
                <input type="search" name="q" id="app-header-search" class="app-header__search-input"
                       placeholder="{{ $searchPlaceholder }}" value="{{ request('q') }}" autocomplete="off">
                <button type="submit" class="app-header__search-btn" aria-label="Lancer la recherche">
                    @include('app.partials.app-nav-icon', ['name' => 'search'])
                </button>
            </form>
        @endif

        <div class="app-header__utilities">
            @if (in_array($slug, ['particulier', 'artisan', 'batiment', 'fournisseur'], true))
                <span class="app-header__profile-tag">{{ $workspaceProfileLabel }}</span>

                <a href="{{ route('app.'.$slug.'.messages') }}"
                   class="app-header__icon-btn {{ $p === 'messages' ? 'is-active' : '' }}"
                   title="Messages" aria-label="Messages">
                    @include('app.partials.app-nav-icon', ['name' => 'chat'])
                </a>

                <a href="{{ route('app.'.$slug.'.notifications') }}"
                   class="app-header__icon-btn app-header__icon-btn--notif {{ $unread > 0 ? 'has-unread' : '' }}"
                   title="{{ $unread > 0 ? $unread.' notification(s) non lue(s)' : 'Notifications' }}"
                   aria-label="{{ $unread > 0 ? 'Notifications, '.$unread.' non lue(s)' : 'Notifications' }}">
                    @include('app.partials.app-nav-icon', ['name' => 'bell'])
                    @if ($unread > 0)
                        <span class="app-header__notif-dot" aria-hidden="true"></span>
                    @endif
                </a>

                @if ($authUser)
                    <a href="{{ route('app.'.$slug.'.profile') }}"
                       class="app-header__user {{ $profileActive ? 'is-active' : '' }}"
                       title="{{ $displayName }}">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="" class="app-header__user-avatar" width="36" height="36" loading="lazy">
                        @else
                            <span class="app-header__user-avatar app-header__user-avatar--initials" aria-hidden="true">{{ $initials }}</span>
                        @endif
                        <span class="app-header__user-name">{{ $displayName }}</span>
                    </a>
                @endif

                <a href="{{ route('app.'.$slug.'.settings') }}"
                   class="app-header__icon-btn {{ $settingsNavActive ? 'is-active' : '' }}"
                   title="Paramètres" aria-label="Paramètres">
                    @include('app.partials.app-nav-icon', ['name' => 'settings'])
                </a>

                <form action="{{ route('app.logout') }}" method="post" class="app-header__logout-form">
                    @csrf
                    <button type="submit" class="app-header__logout-btn" title="Déconnexion" aria-label="Déconnexion">
                        @include('app.partials.app-nav-icon', ['name' => 'logout'])
                        <span class="app-header__logout-label">Déconnexion</span>
                    </button>
                </form>
            @elseif ($authUser)
                <form action="{{ route('app.logout') }}" method="post" class="app-header__logout-form">
                    @csrf
                    <button type="submit" class="app-header__logout-btn" title="Déconnexion" aria-label="Déconnexion">
                        @include('app.partials.app-nav-icon', ['name' => 'logout'])
                        <span class="app-header__logout-label">Déconnexion</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($navLinks !== [])
        <div class="app-header__nav-wrap">
            <nav class="app-header__nav" aria-label="Navigation principale">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}" class="{{ ! empty($link['active']) ? 'is-active' : '' }}">{{ $link['label'] }}</a>
                @endforeach
            </nav>
            <button type="button" class="app-header__nav-more" aria-label="Défiler le menu" hidden>
                <span aria-hidden="true">›</span>
            </button>
        </div>
    @endif
</header>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var wrap = document.querySelector('.app-header__nav-wrap');
    var nav = document.querySelector('.app-header__nav');
    var more = document.querySelector('.app-header__nav-more');
    if (!wrap || !nav || !more) return;

    function updateNavScroll() {
        var overflow = nav.scrollWidth > nav.clientWidth + 2;
        more.hidden = !overflow;
    }

    more.addEventListener('click', function () {
        nav.scrollBy({ left: Math.min(220, nav.clientWidth * 0.6), behavior: 'smooth' });
    });

    updateNavScroll();
    window.addEventListener('resize', updateNavScroll);
});
</script>
@endpush
