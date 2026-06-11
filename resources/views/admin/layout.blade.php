@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user?->name ?? 'A'))
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');

    $navSections = [
        [
            'label' => 'Pilotage',
            'items' => [
                ['route' => 'admin.dashboard', 'params' => [], 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'admin-ico-home'],
                ['route' => 'admin.pending', 'params' => [], 'pattern' => 'admin.pending', 'label' => 'À traiter', 'icon' => 'admin-ico-bell'],
                ['route' => 'admin.reports', 'params' => [], 'pattern' => 'admin.reports', 'label' => 'Statistiques', 'icon' => 'admin-ico-chart'],
            ],
        ],
        [
            'label' => 'Validation des profils',
            'items' => [
                ['route' => 'admin.profile-validation.index', 'params' => ['status' => 'pending'], 'pattern' => null, 'label' => 'Comptes en attente', 'icon' => 'admin-ico-check'],
                ['route' => 'admin.profile-validation.index', 'params' => ['status' => 'approved'], 'pattern' => null, 'label' => 'Comptes validés', 'icon' => 'admin-ico-check'],
                ['route' => 'admin.profile-validation.index', 'params' => ['status' => 'rejected'], 'pattern' => null, 'label' => 'Comptes rejetés', 'icon' => 'admin-ico-x'],
                ['route' => 'admin.profile-validation.index', 'params' => ['status' => 'changes_requested'], 'pattern' => null, 'label' => 'Modifs demandées', 'icon' => 'admin-ico-doc'],
            ],
        ],
        [
            'label' => 'Utilisateurs',
            'items' => [
                ['route' => 'admin.users.index', 'params' => [], 'pattern' => null, 'label' => 'Tous les utilisateurs', 'icon' => 'admin-ico-users'],
                ['route' => 'admin.users.index', 'params' => ['profile_type' => 'entrepreneur_batiment'], 'pattern' => null, 'label' => 'Entreprises BTP', 'icon' => 'admin-ico-store'],
                ['route' => 'admin.users.index', 'params' => ['profile_type' => 'artisan'], 'pattern' => null, 'label' => 'Artisans', 'icon' => 'admin-ico-wrench'],
                ['route' => 'admin.users.index', 'params' => ['profile_type' => 'particulier'], 'pattern' => null, 'label' => 'Particuliers', 'icon' => 'admin-ico-users'],
                ['route' => 'admin.users.index', 'params' => ['profile_type' => 'entreprise_fournisseur'], 'pattern' => null, 'label' => 'Fournisseurs', 'icon' => 'admin-ico-cube'],
            ],
        ],
        [
            'label' => 'Marketplace',
            'items' => [
                ['route' => 'admin.services.index', 'params' => [], 'pattern' => 'admin.services.*', 'label' => 'Services', 'icon' => 'admin-ico-wrench'],
                ['route' => 'admin.besoins.index', 'params' => [], 'pattern' => 'admin.besoins.*', 'label' => 'Recrutement (besoins)', 'icon' => 'admin-ico-store'],
                ['route' => 'admin.candidatures.index', 'params' => [], 'pattern' => 'admin.candidatures.*', 'label' => 'Candidatures', 'icon' => 'admin-ico-clipboard'],
                ['route' => 'admin.products.index', 'params' => [], 'pattern' => 'admin.products.*', 'label' => 'Produits (catalogue)', 'icon' => 'admin-ico-cube'],
            ],
        ],
        [
            'label' => 'Contenu & flux',
            'items' => [
                ['route' => 'admin.activities', 'params' => [], 'pattern' => 'admin.activities', 'label' => 'Activités', 'icon' => 'admin-ico-chart'],
                ['route' => 'admin.devis.index', 'params' => [], 'pattern' => 'admin.devis.*', 'label' => 'Commandes / devis', 'icon' => 'admin-ico-doc'],
                ['route' => 'admin.messages.index', 'params' => [], 'pattern' => 'admin.messages.*', 'label' => 'Messagerie', 'icon' => 'admin-ico-users'],
                ['route' => 'admin.support.tickets.index', 'params' => [], 'pattern' => 'admin.support.tickets*|admin.support', 'label' => 'Support', 'icon' => 'admin-ico-doc'],
                ['route' => 'admin.moderation', 'params' => [], 'pattern' => 'admin.moderation', 'label' => 'Modération', 'icon' => 'admin-ico-bell'],
            ],
        ],
        [
            'label' => 'Configuration',
            'items' => [
                ['route' => 'admin.settings-hub', 'params' => [], 'pattern' => 'admin.settings-hub|admin.categories.*', 'label' => 'Paramétrage', 'icon' => 'admin-ico-doc'],
                ['route' => 'admin.administrators.index', 'params' => [], 'pattern' => 'admin.administrators*', 'label' => 'Administrateurs', 'icon' => 'admin-ico-users'],
            ],
        ],
    ];

    $pendingProfiles = \App\Models\User::query()
        ->where('role', \App\Models\User::ROLE_USER)
        ->whereNotNull('profile_completed_at')
        ->where('profile_validation_status', \App\Models\User::VALIDATION_PENDING)
        ->count();

    $supportTicketsActive = \App\Models\SupportTicket::query()
        ->whereIn('status', [
            \App\Models\SupportTicket::STATUS_OPEN,
            \App\Models\SupportTicket::STATUS_IN_PROGRESS,
        ])
        ->count();

    $pendingTotal = (\App\Models\Product::where('status', 'pending')->count())
        + (\App\Models\Service::where('status', 'pending')->count())
        + $pendingProfiles
        + $supportTicketsActive;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1f3b">
    <title>{{ $title ?? 'Admin' }} — {{ config('app.name', 'BATITRAVOO') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ public_asset('css/admin.css') }}?v=11">
    <link rel="stylesheet" href="{{ public_asset('css/admin-vitrine.css') }}?v=2">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body
    class="admin admin-body"
    x-data="adminShell()"
    x-init="init()"
    :class="{ 'is-collapsed': collapsed && !isMobile, 'is-mobile-open': mobileOpen && isMobile }"
>
@include('admin.partials.icons-sprite')
<div class="admin-shell" :class="{ 'is-collapsed': collapsed && !isMobile, 'is-mobile-open': mobileOpen && isMobile }">
    {{-- ====== SIDEBAR ====== --}}
    <aside class="admin-side" aria-label="Navigation principale">
        <div class="admin-side__brand">
            <a href="{{ route('admin.dashboard') }}" class="admin-side__brand-logo-link" title="BATITRAVOO Admin" aria-label="BATITRAVOO Admin">
                @include('admin.partials.brand-logo')
            </a>
            <div class="admin-side__brand-name">
                BATITRAVOO
                <small>Back-office</small>
            </div>
        </div>

        <div class="admin-side__body">
        @foreach ($navSections as $section)
            <p class="admin-side__section">{{ $section['label'] }}</p>
            <nav class="admin-side__nav" aria-label="{{ $section['label'] }}">
                @foreach ($section['items'] as $item)
                    @php
                        $params = $item['params'] ?? [];
                        $href = route($item['route'], $params);
                        $active = false;
                        if (! empty($item['pattern'])) {
                            $active = request()->routeIs($item['pattern']);
                        } elseif (array_key_exists('status', $params)) {
                            $st = (string) request('status', 'pending');
                            $active = request()->routeIs('admin.profile-validation.index') && $st === (string) $params['status'];
                        } elseif (array_key_exists('profile_type', $params)) {
                            $active = request()->routeIs('admin.users.*') && (string) request('profile_type') === (string) $params['profile_type'];
                        } elseif (($item['route'] ?? '') === 'admin.users.index' && $params === []) {
                            $active = request()->routeIs('admin.users.*') && ! request()->filled('profile_type');
                        }
                    @endphp
                    <a
                        href="{{ $href }}"
                        class="admin-nav-link {{ $active ? 'is-on' : '' }}"
                        data-tooltip="{{ $item['label'] }}"
                        @if ($active) aria-current="page" @endif
                    >
                        <svg class="admin-ico" aria-hidden="true"><use href="#{{ $item['icon'] }}" xlink:href="#{{ $item['icon'] }}"/></svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        @endforeach
        </div>

        <div class="admin-side__bottom">
            <a href="{{ url('/') }}" class="admin-nav-link" data-tooltip="Voir le site" target="_blank" rel="noopener">
                <svg class="admin-ico" aria-hidden="true"><use href="#admin-ico-eye" xlink:href="#admin-ico-eye"/></svg>
                <span>Voir le site</span>
            </a>
        </div>
    </aside>

    <div class="admin-main">
        {{-- ====== HEADER ====== --}}
        <header class="admin-header">
            <button type="button" class="admin-header__toggle" @click="toggleSide()" :aria-label="collapsed ? 'Déployer la navigation' : 'Réduire la navigation'">
                <svg width="18" height="18" aria-hidden="true"><use href="#admin-ico-menu" xlink:href="#admin-ico-menu"/></svg>
            </button>

            <h1 class="admin-header__title">{{ $title ?? '' }}</h1>

            <form method="get" action="{{ route('admin.search') }}" class="admin-header__search" role="search">
                <input
                    type="search"
                    name="q"
                    class="admin-header__search-input"
                    placeholder="Recherche globale..."
                    x-ref="search"
                    value="{{ request('q') }}"
                    aria-label="Recherche globale"
                    autocomplete="off"
                >
                <span class="admin-header__search-ico">
                    <svg width="16" height="16" aria-hidden="true"><use href="#admin-ico-search" xlink:href="#admin-ico-search"/></svg>
                </span>
            </form>

            <div class="admin-header__actions">
                <a href="{{ route('admin.pending') }}" class="admin-icon-btn" title="File d’attente" aria-label="{{ $pendingTotal }} élément(s) en attente — ouvrir la file d’attente">
                    <svg width="18" height="18" aria-hidden="true"><use href="#admin-ico-bell" xlink:href="#admin-ico-bell"/></svg>
                    @if ($pendingTotal > 0)
                        <span class="admin-icon-btn__dot" aria-hidden="true"></span>
                    @endif
                </a>

                <div class="admin-profile" @click.outside="profileOpen = false">
                    <button type="button" class="admin-profile__btn" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen.toString()" aria-haspopup="menu">
                        <span class="admin-profile__avatar" aria-hidden="true">{{ $initials }}</span>
                        <span class="admin-profile__name">{{ $user?->name ?? 'Admin' }}</span>
                        <svg width="14" height="14" aria-hidden="true"><use href="#admin-ico-chevron-down" xlink:href="#admin-ico-chevron-down"/></svg>
                    </button>
                    <div class="admin-menu" x-show="profileOpen" x-transition.origin.top.right :hidden="!profileOpen" role="menu">
                        <div class="admin-menu__head">
                            <strong style="display:block;color:var(--text-1);font-weight:700">{{ $user?->name }}</strong>
                            {{ $user?->email }}
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="admin-menu__item" role="menuitem">
                            <svg width="16" height="16" aria-hidden="true"><use href="#admin-ico-home" xlink:href="#admin-ico-home"/></svg>
                            Tableau de bord
                        </a>
                        <a href="{{ url('/') }}" class="admin-menu__item" role="menuitem" target="_blank" rel="noopener">
                            <svg width="16" height="16" aria-hidden="true"><use href="#admin-ico-eye" xlink:href="#admin-ico-eye"/></svg>
                            Voir le site
                        </a>
                        <div class="admin-menu__sep"></div>
                        <form action="{{ route('admin.logout') }}" method="post" style="margin:0">
                            @csrf
                            <button type="submit" class="admin-menu__item admin-menu__item--danger" role="menuitem">
                                <svg width="16" height="16" aria-hidden="true"><use href="#admin-ico-logout" xlink:href="#admin-ico-logout"/></svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- ====== PAGE ====== --}}
        <main class="admin-page">
            @if(session('ok'))
                <div class="admin-flash admin-flash--ok" role="status">
                    <svg width="18" height="18" aria-hidden="true"><use href="#admin-ico-check" xlink:href="#admin-ico-check"/></svg>
                    <span>{{ session('ok') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="admin-flash admin-flash--err" role="alert">
                    <svg width="18" height="18" aria-hidden="true"><use href="#admin-ico-x" xlink:href="#admin-ico-x"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="card admin-card--alert" role="alert">
                    <ul style="margin:0; padding-left:1.1rem; color:#b91c1c">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script>
    function adminShell() {
        return {
            collapsed: localStorage.getItem('admin.sideCollapsed') === '1',
            mobileOpen: false,
            profileOpen: false,
            isMobile: window.matchMedia('(max-width: 900px)').matches,
            init() {
                window.addEventListener('resize', () => {
                    this.isMobile = window.matchMedia('(max-width: 900px)').matches;
                    if (!this.isMobile) this.mobileOpen = false;
                });
                window.addEventListener('keydown', (e) => {
                    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                        e.preventDefault();
                        this.$refs.search?.focus();
                    }
                    if (e.key === 'Escape') this.profileOpen = false;
                });
            },
            toggleSide() {
                if (this.isMobile) {
                    this.mobileOpen = !this.mobileOpen;
                } else {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('admin.sideCollapsed', this.collapsed ? '1' : '0');
                }
            },
        };
    }
</script>

@stack('scripts')
</body>
</html>
