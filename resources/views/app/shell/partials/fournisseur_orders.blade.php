@php
    $scope = app(\App\Services\DevisScopeService::class);
@endphp

<div class="app-page-stack--split">
    <div class="app-card">
        <div class="app-page-head app-page-head--flush">
            <div class="app-page-head__main">
                <h2 class="app-page-head__title">Mes commandes</h2>
                <p class="app-page-head__desc">Commandes catalogue passées par vos clients (équivalent mobile « Mes commandes »).</p>
            </div>
            <div class="app-page-head__actions">
                <a href="{{ route('app.fournisseur.devis', ['direction' => 'received']) }}" class="app-text-link">Mes devis</a>
            </div>
        </div>
    </div>

    <form method="get" action="{{ route('app.fournisseur.commandes') }}" class="app-filter-bar app-card">
        <div class="app-chip-row devis-chips__row" role="group" aria-label="Filtres statut">
            @foreach ($scope->supplierStatusChipFilters() as $chip)
                @php
                    $val = (string) ($chip['value'] ?? '');
                    $active = (string) request('status', '') === $val;
                @endphp
                <a href="{{ route('app.fournisseur.commandes', array_filter(['status' => $val !== '' ? $val : null])) }}"
                   class="app-chip devis-chip {{ $active ? 'is-active' : '' }}">{{ $chip['label'] }}</a>
            @endforeach
        </div>
    </form>

    <div class="app-card">
        @include('app.shell.partials.devis_list_cards')
    </div>
</div>
