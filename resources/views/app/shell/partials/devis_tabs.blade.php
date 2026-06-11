@php
    use App\Services\DevisScopeService;

    $slug = $profileSlug ?? '';
    $pageKey = $page ?? 'devis';
    $baseUrl = $pageKey === 'fournisseur_orders'
        ? route('app.fournisseur.commandes')
        : route('app.'.$slug.'.devis');
    $direction = $devisDirection ?? DevisScopeService::DIRECTION_RECEIVED;
    $currentKind = $devisKind ?? DevisScopeService::KIND_ALL;
    $scope = app(DevisScopeService::class);

    $tabs = match ($slug) {
        'particulier' => [
            ['direction' => DevisScopeService::DIRECTION_RECEIVED, 'kind' => null, 'label' => 'Devis reçus'],
            ['direction' => DevisScopeService::DIRECTION_SENT, 'kind' => DevisScopeService::KIND_CATALOG, 'label' => 'Mes commandes'],
            ['direction' => DevisScopeService::DIRECTION_SENT, 'kind' => DevisScopeService::KIND_MARKETPLACE, 'label' => 'Mes devis'],
        ],
        'fournisseur' => [],
        'artisan' => [
            ['direction' => DevisScopeService::DIRECTION_RECEIVED, 'kind' => null, 'label' => 'Mes missions'],
            ['direction' => DevisScopeService::DIRECTION_SENT, 'kind' => null, 'label' => 'Devis envoyés'],
        ],
        'batiment' => [
            ['direction' => DevisScopeService::DIRECTION_RECEIVED, 'kind' => null, 'label' => 'Devis reçus'],
            ['direction' => DevisScopeService::DIRECTION_SENT, 'kind' => null, 'label' => 'Devis envoyés'],
        ],
        default => [],
    };

    $statusChips = $slug === 'artisan' && $direction === DevisScopeService::DIRECTION_RECEIVED
        ? $scope->missionChipFilters()
        : $scope->statusChipFilters();
    $chipParam = ($slug === 'artisan' && $direction === DevisScopeService::DIRECTION_RECEIVED) ? 'mission' : 'status';
    $chipValue = request($chipParam, $chipParam === 'mission' ? 'all' : '');
    $statusCounts = is_array($devisList['meta']['status_counts'] ?? null)
        ? $devisList['meta']['status_counts']
        : [];
@endphp

@if ($tabs !== [])
    <nav class="mp-tabs devis-tabs devis-tabs--pill" aria-label="Onglets devis">
        @foreach ($tabs as $tab)
            @php
                $tabDir = $tab['direction'];
                $tabKind = $tab['kind'] ?? null;
                if ($slug === 'particulier') {
                    $isActive = $direction === $tabDir
                        && ($tabKind === null
                            ? $currentKind === DevisScopeService::KIND_ALL
                            : $currentKind === $tabKind);
                } else {
                    $isActive = $direction === $tabDir;
                }
                $query = ['direction' => $tabDir];
                if ($tabKind !== null) {
                    $query['kind'] = $tabKind;
                }
                if ($chipValue !== '' && $chipValue !== 'all') {
                    $query[$chipParam] = $chipValue;
                }
                $tabUrl = $baseUrl.'?'.http_build_query($query);
            @endphp
            <a href="{{ $tabUrl }}" class="mp-tab {{ $isActive ? 'is-active' : '' }}">{{ $tab['label'] }}</a>
        @endforeach
    </nav>
@endif

@if ($pageKey !== 'fournisseur_orders')
    <form method="get" action="{{ $baseUrl }}" class="devis-chips app-mt-sm">
        <input type="hidden" name="direction" value="{{ $direction }}">
        @if ($slug === 'particulier' && $direction === DevisScopeService::DIRECTION_SENT && $currentKind !== DevisScopeService::KIND_ALL)
            <input type="hidden" name="kind" value="{{ $currentKind }}">
        @endif
        <div class="app-chip-row devis-chips__row" role="group" aria-label="Filtres">
            @foreach ($statusChips as $chip)
                @php
                    $val = (string) ($chip['value'] ?? '');
                    $active = (string) $chipValue === $val || ($val === 'all' && ($chipValue === '' || $chipValue === 'all'));
                    $chipQuery = ['direction' => $direction];
                    if ($slug === 'particulier' && $direction === DevisScopeService::DIRECTION_SENT && $currentKind !== DevisScopeService::KIND_ALL) {
                        $chipQuery['kind'] = $currentKind;
                    }
                    if ($val !== '' && $val !== 'all') {
                        $chipQuery[$chipParam] = $val;
                    }
                    $chipUrl = $baseUrl.'?'.http_build_query($chipQuery);
                    $count = (int) ($statusCounts[$val] ?? 0);
                    $chipLabel = $chip['label'].($count > 0 ? ' ('.$count.')' : '');
                @endphp
                <a href="{{ $chipUrl }}" class="app-chip devis-chip {{ $active ? 'is-active' : '' }}">{{ $chipLabel }}</a>
            @endforeach
        </div>
    </form>
@endif
