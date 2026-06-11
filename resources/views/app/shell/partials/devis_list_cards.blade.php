@php
    use App\Services\DevisScopeService;

    $rows = $devisList['data'] ?? [];
    $direction = $devisDirection ?? DevisScopeService::DIRECTION_RECEIVED;
    $slug = $profileSlug ?? '';
    $fromParam = ($page ?? '') === 'fournisseur_orders' ? ['from' => 'commandes'] : ['direction' => $direction];
    $formatDate = function ($iso) {
        if (empty($iso)) {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($iso)->locale('fr')->translatedFormat('d M Y');
        } catch (\Throwable) {
            return (string) $iso;
        }
    };
    $kindClass = fn (string $kind) => match ($kind) {
        'commande_produits' => 'devis-kind--products',
        'demande_devis' => 'devis-kind--quote',
        'besoin_opportunite' => 'devis-kind--besoin',
        'service' => 'devis-kind--service',
        'brouillon_interne' => 'devis-kind--draft',
        default => 'devis-kind--chantier',
    };
@endphp

@if (! empty($rows) && count($rows))
    <div class="devis-list-cards">
        @foreach ($rows as $row)
            @php
                $showUrl = route('app.'.$slug.'.devis.show', array_merge(['devis' => $row['id'] ?? 0], $fromParam));
                $counterparty = $direction === DevisScopeService::DIRECTION_SENT
                    ? ($row['counterparty_name'] ?? ($row['prestataire_name'] ?? $row['client_name'] ?? '—'))
                    : ($row['client_name'] ?? $row['prestataire_name'] ?? '—');
                $kind = (string) ($row['subject_kind'] ?? 'devis_chantier');
            @endphp
            <article class="devis-card app-card">
                <div class="devis-card__head">
                    <span class="devis-kind {{ $kindClass($kind) }}">{{ $row['subject_kind_label'] ?? 'Devis' }}</span>
                    <span class="app-pill">{{ $row['status_label'] ?? ($row['status'] ?? '—') }}</span>
                </div>
                <h3 class="devis-card__title">{{ $row['title'] ?? 'Sans titre' }}</h3>
                <p class="devis-card__meta app-muted">
                    <span>#{{ $row['id'] ?? '—' }}</span>
                    <span>·</span>
                    <span>{{ $counterparty }}</span>
                    @if (! empty($row['order_reference']))
                        <span>·</span>
                        <span>{{ $row['order_reference'] }}</span>
                    @endif
                </p>
                <div class="devis-card__foot">
                    <span class="app-muted">{{ $formatDate($row['created_at'] ?? null) }}</span>
                    @if (! empty($row['total_fcfa_display']))
                        <strong>{{ number_format((int) $row['total_fcfa_display'], 0, ',', ' ') }} FCFA</strong>
                    @endif
                    <a href="{{ $showUrl }}" class="app-text-link">Voir</a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="devis-list-table app-table-wrap">
        <table class="app-table app-table--bordered">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Titre</th>
                    <th>{{ $direction === DevisScopeService::DIRECTION_SENT ? 'Destinataire' : 'Client' }}</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php
                        $showUrl = route('app.'.$slug.'.devis.show', array_merge(['devis' => $row['id'] ?? 0], $fromParam));
                        $counterparty = $direction === DevisScopeService::DIRECTION_SENT
                            ? ($row['counterparty_name'] ?? ($row['prestataire_name'] ?? $row['client_name'] ?? '—'))
                            : ($row['client_name'] ?? $row['prestataire_name'] ?? '—');
                    @endphp
                    <tr>
                        <td><span class="devis-kind devis-kind--inline {{ $kindClass((string) ($row['subject_kind'] ?? '')) }}">{{ $row['subject_kind_label'] ?? '—' }}</span></td>
                        <td>{{ $row['title'] ?? '—' }}</td>
                        <td>{{ $counterparty }}</td>
                        <td>
                            @if (! empty($row['total_fcfa_display']))
                                {{ number_format((int) $row['total_fcfa_display'], 0, ',', ' ') }} FCFA
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="app-pill">{{ $row['status_label'] ?? ($row['status'] ?? '—') }}</span></td>
                        <td class="app-muted">{{ $formatDate($row['created_at'] ?? null) }}</td>
                        <td class="app-table__col-actions">
                            @include('app.shell.partials.table_actions_dropdown', [
                                'menuId' => 'devis-actions-'.($row['id'] ?? $loop->index),
                                'actions' => [
                                    ['type' => 'link', 'label' => 'Voir le détail', 'href' => $showUrl],
                                ],
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="app-muted app-mt">
        @if ($slug === 'particulier')
            Aucun élément pour ces critères. Parcourez le <a href="{{ route('app.particulier.marketplace') }}" class="app-text-link">marketplace</a> pour passer commande ou demander un devis.
        @elseif ($slug === 'fournisseur' && ($page ?? '') === 'fournisseur_orders')
            Aucune commande catalogue reçue pour le moment.
        @else
            Aucun devis pour ces critères.
        @endif
    </p>
@endif
