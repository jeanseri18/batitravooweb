@php
    $vue = $candidatureVue ?? 'recues';
    $mp = $profileSlug;
    $baseCand = route('app.'.$mp.'.candidatures');
    $linkRecues = $baseCand.'?'.http_build_query(['vue' => 'recues']);
    $linkEnvoyees = $baseCand.'?'.http_build_query(['vue' => 'envoyees']);
    $showTabs = $mp === 'batiment';
    $filterBid = (int) ($candidatureBesoinFilter ?? 0);
    $clearFilterUrl = $mp === 'batiment' ? $baseCand.'?vue=recues' : $baseCand;

    $statusCand = static function (?string $s): string {
        return match ($s) {
            'recu' => 'Reçue',
            'accepte' => 'Acceptée',
            'rejete' => 'Refusée',
            default => $s ?? '—',
        };
    };

    $canDecideRecues = $vue === 'recues' && in_array($mp, ['particulier', 'batiment'], true);
@endphp

@if ($showTabs)
    <nav class="mp-tabs devis-tabs--pill app-card app-mt" aria-label="Type de candidatures">
        <a href="{{ $linkRecues }}" class="mp-tab {{ $vue === 'recues' ? 'is-active' : '' }}" @if ($vue === 'recues') aria-current="page" @endif>Reçues sur mes besoins</a>
        <a href="{{ $linkEnvoyees }}" class="mp-tab {{ $vue === 'envoyees' ? 'is-active' : '' }}" @if ($vue === 'envoyees') aria-current="page" @endif>Mes candidatures envoyées</a>
    </nav>
@endif

@if ($filterBid > 0 && $vue === 'recues')
    <div class="app-filter-chip app-card app-mt">
        <span>Filtré sur le besoin <strong>#{{ $filterBid }}</strong></span>
        <a href="{{ $clearFilterUrl }}" class="app-text-link">Afficher toutes les candidatures</a>
    </div>
@endif

<div class="app-card app-mt">
    <h2 class="app-section-title app-mb-sm" style="margin-top:0;">
        @if ($mp === 'particulier')
            Réponses à vos besoins
        @elseif ($mp === 'artisan')
            Projets auxquels vous avez postulé
        @elseif ($mp === 'fournisseur')
            Besoins chantier auxquels vous avez candidaté
        @elseif ($mp === 'batiment' && $vue === 'recues')
            Candidatures reçues sur vos chantiers
        @elseif ($mp === 'batiment')
            Vos candidatures sur les besoins du marketplace
        @else
            Candidatures
        @endif
    </h2>

    @if (! empty($candidaturesList) && count($candidaturesList))
        <div class="app-table-wrap">
            <table class="app-table app-table--bordered">
                <thead>
                    <tr>
                        @if ($vue === 'recues')
                            <th>Candidat</th>
                            <th>Besoin</th>
                            <th>Message</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="app-table__col-actions">Actions</th>
                        @else
                            <th>Besoin</th>
                            <th>Message envoyé</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="app-table__col-actions">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($candidaturesList as $row)
                        @php
                            $besoinTitle = $row['besoin']['title'] ?? ('Besoin #'.($row['besoin_id'] ?? ''));
                            $posted = isset($row['posted_at']) ? \Illuminate\Support\Str::limit((string) $row['posted_at'], 16, '') : '—';
                            $msgExcerpt = isset($row['message']) ? \Illuminate\Support\Str::limit(strip_tags((string) $row['message']), 72) : '—';
                            $cid = (int) ($row['id'] ?? 0);
                            $st = $row['status'] ?? '';
                            $peer = (int) ($row['applicant_id'] ?? 0);
                        @endphp
                        @if ($vue === 'recues')
                            <tr>
                                <td><strong>{{ $row['display_name'] ?? '—' }}</strong></td>
                                <td>{{ $besoinTitle }}</td>
                                <td class="app-muted">{{ $msgExcerpt }}</td>
                                <td><span class="app-pill">{{ $statusCand($st) }}</span></td>
                                <td class="app-muted">{{ $posted }}</td>
                                <td class="app-table__col-actions">
                                    @php
                                        $candActions = [];
                                        if ($peer > 0) {
                                            $candActions[] = ['type' => 'link', 'label' => 'Message', 'href' => route('app.'.$mp.'.messages', ['peer_id' => $peer])];
                                        }
                                        if ($canDecideRecues && $st === 'recu' && $cid > 0) {
                                            $candActions[] = ['type' => 'form', 'label' => 'Accepter', 'action' => route('app.'.$mp.'.candidatures.status', ['candidature' => $cid]), 'hidden' => ['status' => 'accepte']];
                                            $candActions[] = ['type' => 'form', 'label' => 'Refuser', 'action' => route('app.'.$mp.'.candidatures.status', ['candidature' => $cid]), 'hidden' => ['status' => 'rejete'], 'danger' => true];
                                        }
                                    @endphp
                                    @include('app.shell.partials.table_actions_dropdown', [
                                        'menuId' => 'cand-recues-'.$cid,
                                        'actions' => $candActions,
                                    ])
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td><strong>{{ $besoinTitle }}</strong></td>
                                <td class="app-muted">{{ $msgExcerpt }}</td>
                                <td><span class="app-pill">{{ $statusCand($st) }}</span></td>
                                <td class="app-muted">{{ $posted }}</td>
                                <td class="app-table__col-actions">
                                    @if (! empty($row['besoin_id']))
                                        @include('app.shell.partials.table_actions_dropdown', [
                                            'menuId' => 'cand-envoyees-'.(int) $row['besoin_id'].'-'.$loop->index,
                                            'actions' => [
                                                ['type' => 'link', 'label' => 'Voir le besoin', 'href' => route('app.'.$mp.'.marketplace.besoin', ['besoin' => (int) $row['besoin_id']])],
                                            ],
                                        ])
                                    @else
                                        <span class="app-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="app-muted app-mb-sm">
            @if ($vue === 'recues' && $mp === 'particulier')
                <p>Aucune candidature pour l’instant. Publiez un besoin ou élargissez la description pour attirer les artisans.</p>
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.75rem;">
                    <a href="{{ route('app.'.$mp.'.besoins.create') }}" class="app-btn app-btn--inline">Publier un besoin</a>
                    <a href="{{ route('app.'.$mp.'.besoins') }}" class="app-btn app-btn--secondary app-btn--inline">Mes besoins</a>
                </div>
            @elseif ($vue === 'envoyees' && $mp === 'artisan')
                <p class="app-muted app-mb-sm">Aucune candidature envoyée.</p>
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.75rem;">
                    <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--inline">Voir les besoins</a>
                    <a href="{{ route('app.'.$mp.'.services') }}" class="app-btn app-btn--secondary app-btn--inline">Mes prestations</a>
                </div>
            @elseif ($vue === 'envoyees' && $mp === 'fournisseur')
                <p class="app-muted app-mb-sm">Aucune candidature.</p>
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.75rem;">
                    <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--inline">Besoins chantier</a>
                    <a href="{{ route('app.'.$mp.'.products') }}" class="app-btn app-btn--secondary app-btn--inline">Mon catalogue</a>
                </div>
            @elseif ($vue === 'recues' && $mp === 'batiment')
                <p>Aucune candidature sur vos besoins pour ces critères.</p>
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.75rem;">
                    <a href="{{ route('app.'.$mp.'.besoins.create') }}" class="app-btn app-btn--inline">Nouveau besoin</a>
                    <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--secondary app-btn--inline">Marketplace</a>
                </div>
            @elseif ($vue === 'envoyees' && $mp === 'batiment')
                <p>Aucune candidature envoyée enregistrée.</p>
                <a href="{{ route('app.'.$mp.'.marketplace', ['tab' => 'besoins']) }}" class="app-btn app-btn--inline app-mt-sm">Parcourir les besoins</a>
            @else
                <p>Aucune entrée pour cette vue.</p>
            @endif
        </div>
    @endif
</div>
