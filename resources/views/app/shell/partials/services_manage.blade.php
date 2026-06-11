@php
    $mp = $profileSlug;
    $headTitle = match ($mp) {
        'artisan' => 'Mes prestations artisan',
        'batiment' => 'Prestations entreprise BTP',
        default => 'Mes services',
    };
    $visibilityLabel = static function (array $row): string {
        $visible = $row['is_visible'] ?? true;

        return $visible ? 'Visible' : 'Masqué';
    };
    $priceShort = static function (array $row): string {
        $pr = $row['pricing'] ?? [];
        $line = $pr['detail_fr'] ?? $pr['title_fr'] ?? '';
        if ($line === '' && ! empty($row['price_fixed_label'])) {
            $line = (string) $row['price_fixed_label'];
        }
        return $line !== '' ? \Illuminate\Support\Str::limit(strip_tags($line), 48) : '—';
    };
@endphp

<div class="app-page-stack--split">
<div class="app-manage-toolbar app-card">
    <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'services']) }}" class="app-btn app-btn--secondary app-btn--sm">Marketplace services</a>
    @if ($mp === 'artisan')
        <a href="{{ route('app.artisan.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--secondary app-btn--sm">Opportunités</a>
    @else
        <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--secondary app-btn--sm">Besoins à pourvoir</a>
    @endif
    <a href="{{ route('app.'.$mp.'.devis') }}" class="app-btn app-btn--secondary app-btn--sm">Gestion des devis</a>
    @if ($mp === 'batiment')
        <a href="{{ route('app.'.$mp.'.besoins') }}" class="app-btn app-btn--secondary app-btn--sm">Mes besoins publiés</a>
    @endif
</div>

<div class="app-card">
    <div class="app-page-head">
        <div class="app-page-head__main">
            <h2 class="app-page-head__title">{{ $headTitle }}</h2>
            <p class="app-page-head__desc">Consultez, modifiez et publiez vos prestations sur le marketplace.</p>
        </div>
        <div class="app-page-head__actions">
            @if (in_array($mp, ['batiment', 'artisan'], true))
                <a href="{{ route('app.'.$mp.'.services.create') }}" class="app-btn app-btn--inline app-btn--sm">Nouvelle prestation</a>
            @else
                <span class="app-muted app-text-sm">Ajout via l’app mobile</span>
            @endif
        </div>
    </div>
    @if (! empty($servicesList) && count($servicesList))
        <div class="app-table-wrap">
            <table class="app-table app-table--bordered">
                <thead>
                    <tr>
                        <th>Prestation</th>
                        <th>Catégorie</th>
                        <th>Type</th>
                        <th>Prix / tarif</th>
                        <th>Lieu</th>
                        <th>Visibilité</th>
                        <th class="app-table__col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servicesList as $row)
                        @php
                            $kind = $row['service_kind'] ?? '';
                            $kindLabel = match ($kind) {
                                'artisan' => 'Artisan',
                                'entrepreneur' => 'Entrepreneur BTP',
                                default => $kind,
                            };
                            $sid = (int) ($row['id'] ?? 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $row['title'] ?? '—' }}</strong></td>
                            <td class="app-muted">{{ $row['category']['name'] ?? '—' }}</td>
                            <td>{{ $kindLabel }}</td>
                            <td class="app-muted">{{ $priceShort($row) }}</td>
                            <td class="app-muted">{{ $row['location'] ?? '—' }}</td>
                            <td><span class="app-pill">{{ $visibilityLabel($row) }}</span></td>
                            <td class="app-table__col-actions">
                                @if ($sid > 0)
                                    @php
                                        $serviceActions = [
                                            ['type' => 'link', 'label' => 'Voir annonce', 'href' => route('app.'.$mp.'.marketplace.service', ['service' => $sid])],
                                        ];
                                        if (in_array($mp, ['batiment', 'artisan'], true)) {
                                            $serviceActions[] = ['type' => 'link', 'label' => 'Modifier', 'href' => route('app.'.$mp.'.services.edit', ['service' => $sid])];
                                            $serviceActions[] = ['type' => 'form', 'label' => 'Supprimer', 'action' => route('app.'.$mp.'.services.destroy', ['service' => $sid]), 'method' => 'DELETE', 'confirm' => 'Supprimer cette prestation ?', 'danger' => true];
                                        }
                                    @endphp
                                    <div class="app-table__col-actions-inner">
                                        @include('app.shell.partials.table_actions_dropdown', [
                                            'menuId' => 'service-actions-'.$sid,
                                            'actions' => $serviceActions,
                                        ])
                                        @if (($row['rating'] ?? 0) > 0)
                                            <span class="app-muted app-table__col-actions-meta" title="Note">{{ number_format((float) $row['rating'], 1, ',', ' ') }} ★</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="app-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="app-muted app-mb-sm">
            @if ($mp === 'artisan')
                Aucune prestation : créez votre première offre ci-dessus ou depuis « Nouvelle prestation » pour apparaître dans les annonces.
            @elseif ($mp === 'batiment')
                Aucune prestation : utilisez « Nouvelle prestation » pour publier vos savoir-faire BTP sur le marketplace.
            @else
                Suivi des prestations listées ci-dessous après publication depuis votre espace.
            @endif
        </p>
        <div class="app-actions-row">
            @if ($mp === 'artisan')
                <a href="{{ route('app.artisan.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--inline">Voir les opportunités</a>
            @else
                <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--inline">Voir les besoins publiés</a>
            @endif
        </div>
    @endif
</div>
</div>
