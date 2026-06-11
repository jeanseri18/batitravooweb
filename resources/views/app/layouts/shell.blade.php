<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-web.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/app-web-vitrine.css') }}?v=37">
    <link rel="stylesheet" href="{{ asset('css/app-web-mockup.css') }}?v=12">
</head>
<body class="app-body app-body--workspace app-body--header-nav" data-app-profile="{{ $profileSlug ?? '' }}">
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
    <div class="app-shell app-shell--header-nav">
        <div class="app-main app-main--full">
            @include('app.shell.partials.nav_header')

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

                @if (! empty($title))
                    <h1 class="app-content__page-title" id="page-title">{{ $title }}</h1>
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
