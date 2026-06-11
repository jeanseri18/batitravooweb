@php
    use App\Services\DevisScopeService;

    $slug = $profileSlug ?? '';
    $direction = $devisDirection ?? DevisScopeService::DIRECTION_RECEIVED;

    $kind = $devisKind ?? DevisScopeService::KIND_ALL;
    $devisTitle = match (true) {
        $slug === 'particulier' && $direction === DevisScopeService::DIRECTION_SENT && $kind === DevisScopeService::KIND_MARKETPLACE => 'Mes devis',
        $slug === 'particulier' && $direction === DevisScopeService::DIRECTION_SENT => 'Mes commandes',
        $slug === 'particulier' => 'Devis reçus',
        $slug === 'artisan' && $direction === DevisScopeService::DIRECTION_SENT => 'Devis envoyés',
        $slug === 'artisan' => 'Mes missions',
        $slug === 'fournisseur' => 'Mes devis',
        $direction === DevisScopeService::DIRECTION_SENT => 'Devis envoyés',
        default => 'Devis reçus',
    };

    $devisDesc = match (true) {
        $slug === 'particulier' && $direction === DevisScopeService::DIRECTION_RECEIVED
            => 'Réponses des prestataires à vos demandes. Pour solliciter un devis, utilisez le marketplace.',
        $slug === 'particulier' && $direction === DevisScopeService::DIRECTION_SENT && $kind === DevisScopeService::KIND_CATALOG
            => 'Commandes matériaux et achats catalogue (panier fournisseur).',
        $slug === 'particulier' && $direction === DevisScopeService::DIRECTION_SENT && $kind === DevisScopeService::KIND_MARKETPLACE
            => 'Demandes de devis envoyées aux artisans et entreprises BTP (hors commandes catalogue).',
        default => null,
    };
    $devisDescFournisseur = $slug === 'fournisseur' && $direction === DevisScopeService::DIRECTION_RECEIVED;
@endphp

<div class="app-page-stack--split">
    <div class="app-card">
        <div class="app-page-head">
            <div class="app-page-head__main">
                <h2 class="app-page-head__title">{{ $devisTitle }}</h2>
                @if ($devisDesc)
                    <p class="app-page-head__desc">{{ $devisDesc }}</p>
                @elseif ($devisDescFournisseur)
                    <p class="app-page-head__desc">Demandes de devis et propositions en cours, hors commandes catalogue. Suivez les <a href="{{ route('app.fournisseur.commandes') }}" class="app-text-link">commandes clients</a> sur une page dédiée.</p>
                @endif
            </div>
            @if ($slug === 'fournisseur')
                <div class="app-page-head__actions">
                    <a href="{{ route('app.fournisseur.commandes') }}" class="app-btn app-btn--secondary app-btn--sm app-btn--inline">Mes commandes</a>
                </div>
            @endif
        </div>

        @include('app.shell.partials.devis_tabs')
    </div>

    @if ($slug === 'fournisseur')
        @php $scope = app(\App\Services\DevisScopeService::class); @endphp
        <form method="get" action="{{ route('app.fournisseur.devis') }}" class="app-filter-bar app-card">
            <div class="app-chip-row devis-chips__row" role="group" aria-label="Filtres statut">
                @foreach ($scope->supplierStatusChipFilters() as $chip)
                    @php
                        $val = (string) ($chip['value'] ?? '');
                        $active = (string) request('status', '') === $val;
                    @endphp
                    <a href="{{ route('app.fournisseur.devis', array_filter(['status' => $val !== '' ? $val : null])) }}"
                       class="app-chip devis-chip {{ $active ? 'is-active' : '' }}">{{ $chip['label'] }}</a>
                @endforeach
            </div>
        </form>
    @endif

    <div class="app-card">
        @include('app.shell.partials.devis_list_cards')
    </div>
</div>
