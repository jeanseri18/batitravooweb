@php
    $u = $profileData['user'] ?? [];
    $slug = $profileSlug ?? '';
    $company = trim((string) ($u['company_name'] ?? ''));
    $useCompany = in_array($slug, ['batiment', 'fournisseur'], true) && $company !== '';
    $displayName = $useCompany ? $company : trim((string) ($u['name'] ?? 'Membre'));
    if ($displayName === '') {
        $displayName = 'Membre';
    }

    $profileComplete = auth()->user()?->profile_completed_at !== null;

    $heroMeta = match ($slug) {
        'particulier' => [
            'title' => 'Particulier & travaux',
            'subtitle' => 'Trouvez prestataires, matériaux et suivez vos commandes en toute simplicité.',
            'deco' => ['Devis', 'Travaux', 'Chantier', 'Matériaux'],
        ],
        'artisan' => [
            'title' => 'Activité artisan',
            'subtitle' => 'Publiez vos services, répondez aux missions et développez votre clientèle.',
            'deco' => ['Services', 'Missions', 'Artisan', 'Devis'],
        ],
        'batiment' => [
            'title' => 'Entreprise BTP',
            'subtitle' => 'Publiez vos besoins, gérez les candidatures et pilotez vos chantiers.',
            'deco' => ['Chantier', 'Besoins', 'BTP', 'Équipes'],
        ],
        'fournisseur' => [
            'title' => 'Catalogue fournisseur',
            'subtitle' => 'Vendez vos matériaux, gérez stock et commandes depuis votre espace.',
            'deco' => ['Produits', 'Stock', 'Commandes', 'Catalogue'],
        ],
        default => [
            'title' => 'Espace membre',
            'subtitle' => 'Bienvenue sur votre tableau de bord Batitravoo.',
            'deco' => ['BTP', 'Devis', 'Travaux', 'Pro'],
        ],
    };

    $heroTitle = $heroTitle ?? $heroMeta['title'];
    $heroSubtitle = $heroSubtitle ?? $heroMeta['subtitle'];
    $heroCtaLabel = $heroCtaLabel ?? null;
    $heroCtaUrl = $heroCtaUrl ?? null;

    $helpRoute = match ($slug) {
        'particulier' => route('app.particulier.help'),
        'artisan' => route('app.artisan.help'),
        'batiment' => route('app.batiment.help'),
        'fournisseur' => route('app.fournisseur.help'),
        default => route('app.'.$slug.'.home'),
    };
@endphp

<section class="app-home-hero app-home-hero--banner" aria-label="Bienvenue">
    <div class="app-home-hero__deco app-home-hero__deco--right" aria-hidden="true">
        <span class="app-home-hero__amp">&amp;</span>
        <span class="app-home-hero__grid"></span>
    </div>

    <div class="app-home-hero__content">
        <p class="app-home-hero__eyebrow">Bonjour, <strong>{{ $displayName }}</strong></p>
        <h1 class="app-home-hero__title">{{ $heroTitle }}</h1>
        <p class="app-home-hero__subtitle">{{ $heroSubtitle }}</p>

        @if (! $profileComplete)
            <p class="app-home-hero__hint">
                <a href="{{ route('app.'.$slug.'.profile') }}" class="app-home-hero__hint-link">Complétez votre profil</a> pour profiter pleinement de la plateforme.
            </p>
        @endif

        <div class="app-home-hero__ctas">
            <a href="{{ $helpRoute }}" class="app-home-hero__cta app-home-hero__cta--ghost">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z"/></svg>
                Comment ça marche
            </a>
            @if ($heroCtaUrl && $heroCtaLabel)
                <a href="{{ $heroCtaUrl }}" class="app-home-hero__cta app-home-hero__cta--primary">{{ $heroCtaLabel }}</a>
            @endif
        </div>
    </div>
</section>
