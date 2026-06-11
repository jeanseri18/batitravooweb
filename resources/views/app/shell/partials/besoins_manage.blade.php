@php
    $mp = $profileSlug;
    $sectionTitle = match ($mp) {
        'particulier' => 'Vos besoins & projets',
        'batiment' => 'Besoins & chantiers publiés',
        default => 'Vos besoins',
    };
    $candBase = route('app.'.$mp.'.candidatures');
    $mkBesoinCandLink = static function (int $besoinId) use ($mp, $candBase): string {
        if ($mp === 'batiment') {
            return $candBase.'?'.http_build_query(['vue' => 'recues', 'besoin_id' => $besoinId]);
        }

        return $candBase.'?'.http_build_query(['besoin_id' => $besoinId]);
    };
    $statusBesoin = static function (?string $s): string {
        return match ($s) {
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'closed' => 'Clôturé',
            'cancelled' => 'Annulé',
            default => $s ?? '—',
        };
    };
@endphp

<div class="app-page-stack--split">
<div class="app-manage-toolbar app-card">
    <a href="{{ route('app.'.$mp.'.candidatures') }}" class="app-btn app-btn--secondary app-btn--sm">Voir les candidatures</a>
    <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--secondary app-btn--sm">Marketplace besoins</a>
</div>

<div class="app-card">
    <div class="app-page-head">
        <div class="app-page-head__main">
            <h2 class="app-page-head__title">{{ $sectionTitle }}</h2>
            <p class="app-page-head__desc">Publiez et suivez vos besoins, puis consultez les candidatures reçues.</p>
        </div>
        <div class="app-page-head__actions">
            <a href="{{ route('app.'.$mp.'.besoins.create') }}" class="app-btn app-btn--inline app-btn--sm">Nouveau besoin</a>
        </div>
    </div>
    @if (! empty($besoinsList) && count($besoinsList))
        <div class="app-table-wrap">
            <table class="app-table app-table--bordered">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Budget</th>
                        <th>Début</th>
                        <th>Lieu</th>
                        <th>Réponses</th>
                        <th>Statut</th>
                        <th class="app-table__col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($besoinsList as $row)
                        @php
                            $bid = (int) ($row['id'] ?? 0);
                            $nCand = (int) ($row['candidature_count'] ?? 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $row['title'] ?? '—' }}</strong></td>
                            <td class="app-muted">{{ $row['budget'] ?? '—' }}</td>
                            <td class="app-muted">{{ $row['start_label'] ?? ($row['short_date'] ?? '—') }}</td>
                            <td class="app-muted">{{ $row['place'] ?? '—' }}</td>
                            <td>
                                @if ($nCand > 0 && $bid > 0)
                                    <a href="{{ $mkBesoinCandLink($bid) }}" class="app-text-link">{{ $nCand }}</a>
                                @else
                                    {{ $nCand }}
                                @endif
                            </td>
                            <td><span class="app-pill">{{ $statusBesoin($row['status'] ?? null) }}</span></td>
                            <td class="app-table__col-actions">
                                @php
                                    $besoinActions = [
                                        ['type' => 'link', 'label' => 'Fiche publique', 'href' => route('app.'.$mp.'.marketplace.besoin', ['besoin' => $bid])],
                                    ];
                                    if ($bid > 0 && $nCand > 0) {
                                        $besoinActions[] = ['type' => 'link', 'label' => 'Candidatures', 'href' => $mkBesoinCandLink($bid)];
                                    }
                                    if ($bid > 0 && in_array($mp, ['particulier', 'batiment'], true)) {
                                        $besoinActions[] = ['type' => 'link', 'label' => 'Modifier', 'href' => route('app.'.$mp.'.besoins.edit', ['besoin' => $bid])];
                                        $besoinActions[] = ['type' => 'form', 'label' => 'Supprimer', 'action' => route('app.'.$mp.'.besoins.destroy', ['besoin' => $bid]), 'method' => 'DELETE', 'confirm' => 'Supprimer ce besoin ?', 'danger' => true];
                                    }
                                @endphp
                                @include('app.shell.partials.table_actions_dropdown', [
                                    'menuId' => 'besoin-actions-'.$bid,
                                    'actions' => $besoinActions,
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="app-muted app-mb-sm">Aucun besoin publié pour le moment.</p>
        <div class="app-actions-row">
            <a href="{{ route('app.'.$mp.'.besoins.create') }}" class="app-btn app-btn--inline">Publier un besoin</a>
            <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--secondary app-btn--inline">Voir le marketplace</a>
        </div>
    @endif
</div>
</div>
