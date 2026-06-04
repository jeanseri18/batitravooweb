<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-web.css') }}">
</head>
<body class="app-body app-body--workspace" data-app-profile="{{ $profileSlug ?? '' }}">
    @include('app.partials.app-web-nav-sprite')
    <a href="#app-main-content" class="app-skip-link">Aller au contenu</a>
    @php
        $workspaceProfileLabel = match ($profileSlug ?? '') {
            'particulier' => 'Particulier',
            'artisan' => 'Artisan',
            'batiment' => 'Entreprise BTP',
            'fournisseur' => 'Fournisseur',
            default => 'Membre',
        };
    @endphp
    <div class="app-shell">
        <aside class="app-sidebar" aria-label="Menu">
            <div class="app-sidebar__header">
                <a href="{{ route('app.'.$profileSlug.'.home') }}" class="app-sidebar__brand">
                    @include('app.partials.brand-logo', ['size' => 'sidebar'])
                </a>
                @php
                    $eyebrow = match ($profileSlug ?? '') {
                        'particulier' => 'Particulier — client',
                        'artisan' => 'Artisan',
                        'batiment' => 'Entreprise BTP',
                        'fournisseur' => 'Fournisseur matériaux',
                        default => 'Membre',
                    };
                @endphp
                <p class="app-sidebar__eyebrow">{{ $eyebrow }}</p>
            </div>
            <div class="app-sidebar__scroll">
            @if (in_array($profileSlug ?? '', ['particulier', 'artisan', 'batiment', 'fournisseur'], true))
                @include('app.shell.partials.nav_sidebar_primary')
                @include('app.shell.partials.nav_sidebar_secondary')
            @else
                <nav class="app-nav app-nav--primary app-nav--profile-menu" aria-label="Navigation principale">
                    <a href="{{ route('app.'.$profileSlug.'.home') }}" class="{{ $page === 'home' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'home'])<span>Accueil</span></a>
                    <a href="{{ route('app.'.$profileSlug.'.dashboard') }}" class="{{ $page === 'dashboard_tab' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'chart'])<span>Tableau de bord</span></a>
                    <a href="{{ route('app.'.$profileSlug.'.marketplace') }}" class="app-nav__cta {{ $page === 'marketplace' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'clipboard'])<span>Annonces</span></a>
                    <a href="{{ route('app.'.$profileSlug.'.messages') }}" class="{{ $page === 'messages' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'chat'])<span>Messages</span></a>
                    <a href="{{ route('app.'.$profileSlug.'.profile') }}" class="{{ $page === 'profile' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'user'])<span>Profil</span></a>
                </nav>
                <nav class="app-nav app-nav--secondary" aria-label="Autres liens">
                    <a href="{{ route('app.'.$profileSlug.'.support') }}" class="{{ str_starts_with($page, 'support') ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'support'])<span>Support</span></a>
                    <a href="{{ route('app.'.$profileSlug.'.notifications') }}" class="{{ $page === 'notifications' ? 'is-active' : '' }}">@include('app.partials.app-nav-icon', ['name' => 'bell'])<span>Notifications</span></a>
                </nav>
            @endif
            </div>
        </aside>
        <div class="app-main">
            <header class="app-topbar">
                <div class="app-topbar__title-wrap">
                    <p class="app-topbar__eyebrow">
                        <span class="app-topbar__eyebrow-muted">Espace membre</span>
                        <span class="app-topbar__eyebrow-sep" aria-hidden="true">/</span>
                        <span class="app-topbar__eyebrow-profile">{{ $workspaceProfileLabel }}</span>
                    </p>
                    <h1 class="app-topbar__title" id="page-title">{{ $title }}</h1>
                </div>
                <div class="app-topbar__actions">
                    @if (($unreadNotifications ?? 0) > 0)
                        <a href="{{ route('app.'.$profileSlug.'.notifications') }}" class="app-badge app-badge--pulse" title="Notifications non lues">{{ $unreadNotifications }} nouvelle(s)</a>
                    @endif
                    <a href="{{ url('/') }}" class="app-topbar__link app-topbar__link--external">Site vitrine</a>
                    <form action="{{ route('app.logout') }}" method="post" class="app-topbar__logout-form">
                        @csrf
                        <button type="submit" class="app-btn app-btn--sm app-btn--ghost-dark">Déconnexion</button>
                    </form>
                </div>
            </header>
            <main class="app-content" id="app-main-content" tabindex="-1">
                <div class="app-content__inner app-page-inner">
                @if (session('status'))
                    <div class="app-alert app-alert--success" role="status">{{ session('status') }}</div>
                @endif
                @if (auth()->check() && ! auth()->user()->profile_completed_at && empty($hideIncompleteProfileBanner))
                    <div class="app-alert app-alert--warn" role="status">
                        <span class="app-alert__text">Votre profil n’est pas encore complété.</span>
                        <a href="{{ route('app.complete-profile') }}" class="app-alert__action">Compléter mon profil</a>
                    </div>
                @endif
                @if (auth()->check() && auth()->user()->profile_completed_at && (auth()->user()->profile_validation_status ?? 'approved') === 'pending')
                    <div class="app-alert app-alert--warn" role="status">
                        <span class="app-alert__text">Votre compte est en cours de validation.</span>
                        <a href="{{ route('app.complete-profile') }}" class="app-alert__action">Voir mon dossier</a>
                    </div>
                @endif
                @if (auth()->check() && auth()->user()->profile_completed_at && (auth()->user()->profile_validation_status ?? 'approved') === 'changes_requested')
                    <div class="app-alert app-alert--warn" role="status">
                        <span class="app-alert__text">Des modifications sont demandées sur votre dossier de validation.</span>
                        <a href="{{ route('app.complete-profile') }}" class="app-alert__action">Corriger mon profil</a>
                    </div>
                @endif
                @if (auth()->check() && auth()->user()->profile_completed_at && (auth()->user()->profile_validation_status ?? 'approved') === 'rejected')
                    <div class="app-alert app-alert--error" role="status">
                        <span class="app-alert__text">Votre dossier a été rejeté. Vous pouvez le compléter à nouveau et le renvoyer.</span>
                        <a href="{{ route('app.complete-profile') }}" class="app-alert__action">Mettre à jour mon profil</a>
                    </div>
                @endif
                @yield('content')
                @include('app.partials.legal-footer')
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
