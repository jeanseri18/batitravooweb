@php
    $slug = $profileSlug ?? '';
    $p = $page ?? '';
    $u = ($profileData ?? [])['user'] ?? null;
    $userName = is_array($u) ? ($u['name'] ?? 'Mon compte') : 'Mon compte';
    $userEmail = is_array($u) ? ($u['email'] ?? '') : '';

    $supportLabel = $slug === 'particulier' ? 'Service client' : 'Support';
    $supportRoute = $slug === 'particulier'
        ? route('app.particulier.service_client')
        : route('app.'.$slug.'.support');

    $items = [
        [
            'route' => route('app.'.$slug.'.profile'),
            'icon' => 'user',
            'title' => 'Profil',
            'description' => 'Modifiez votre nom, photo et coordonnées affichées sur la plateforme.',
            'active' => $p === 'profile',
        ],
        [
            'route' => route('app.'.$slug.'.notifications'),
            'icon' => 'bell',
            'title' => 'Notifications',
            'description' => 'Retrouvez vos alertes : messages, devis, commandes et mises à jour.',
            'active' => $p === 'notifications',
        ],
        [
            'route' => route('app.'.$slug.'.devis'),
            'icon' => 'document',
            'title' => 'Gestion des devis',
            'description' => 'Consultez vos devis reçus, envoyés et répondez aux demandes.',
            'active' => str_starts_with($p, 'devis') || $p === 'fournisseur_orders',
        ],
        [
            'route' => $supportRoute,
            'icon' => 'support',
            'title' => $supportLabel,
            'description' => $slug === 'particulier'
                ? 'Contactez le service client et suivez l’état de vos demandes.'
                : 'Ouvrez un ticket et suivez vos échanges avec l’équipe support.',
            'active' => $slug === 'particulier'
                ? ($p === 'service_client' || str_starts_with($p, 'support'))
                : str_starts_with($p, 'support'),
        ],
        [
            'route' => route('app.'.$slug.'.profile.password.page'),
            'icon' => 'lock',
            'title' => 'Mot de passe',
            'description' => 'Mettez à jour votre mot de passe pour sécuriser votre compte.',
            'active' => $p === 'profile_password',
        ],
        [
            'route' => route('app.'.$slug.'.help'),
            'icon' => 'help',
            'title' => 'Centre d’aide',
            'description' => 'FAQ, guides d’utilisation et réponses aux questions fréquentes.',
            'active' => in_array($p, ['help_particulier', 'help_artisan', 'help_batiment', 'help_fournisseur'], true),
        ],
    ];

    if ($slug !== 'particulier') {
        $items[] = [
            'route' => route('app.'.$slug.'.documents'),
            'icon' => 'paperclip',
            'title' => 'Documents',
            'description' => 'Ajoutez et consultez vos pièces justificatives (Kbis, assurances, etc.).',
            'active' => $p === 'documents',
        ];
    }

    if (in_array($slug, ['batiment', 'fournisseur'], true)) {
        array_splice($items, 1, 0, [[
            'route' => route('app.'.$slug.'.public_preview'),
            'icon' => 'eye',
            'title' => 'Vue publique',
            'description' => 'Prévisualisez la fiche que voient les clients sur le marketplace.',
            'active' => in_array($p, ['vue_publique', 'vue_publique_batiment'], true),
        ]]);
        array_splice($items, 5, 0, [[
            'route' => route('app.'.$slug.'.profile.location.page'),
            'icon' => 'globe',
            'title' => 'Localisation',
            'description' => 'Renseignez votre adresse et la zone géographique couverte.',
            'active' => $p === 'profile_location',
        ]]);
    }
@endphp

<div class="app-card app-settings-hub">
    <div class="app-page-head app-page-head--flush">
        <div class="app-page-head__main">
            <h2 class="app-page-head__title">Paramètres</h2>
            @if ($userName !== 'Mon compte' || $userEmail !== '')
                <p class="app-page-head__desc">
                    {{ $userName }}@if ($userEmail !== '') <span class="app-settings-hub__email">— {{ $userEmail }}</span>@endif
                </p>
            @else
                <p class="app-page-head__desc">Gérez votre compte, la sécurité et l’assistance depuis cette page.</p>
            @endif
        </div>
    </div>

    <div class="app-settings-hub__grid">
        @foreach ($items as $item)
            <a href="{{ $item['route'] }}" class="app-settings-card {{ ! empty($item['active']) ? 'is-active' : '' }}">
                <span class="app-settings-card__icon" aria-hidden="true">
                    @include('app.partials.app-nav-icon', ['name' => $item['icon']])
                </span>
                <span class="app-settings-card__body">
                    <span class="app-settings-card__title">{{ $item['title'] }}</span>
                    <span class="app-settings-card__desc">{{ $item['description'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="app-settings-hub__logout app-mt-md">
        <form action="{{ route('app.logout') }}" method="post">
            @csrf
            <button type="submit" class="app-btn app-btn--ghost app-btn--inline app-settings-hub__logout-btn">Déconnexion</button>
        </form>
    </div>
</div>
