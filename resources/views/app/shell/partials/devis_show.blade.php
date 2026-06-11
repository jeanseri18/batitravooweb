@php
    $d = $devisDetail['data'] ?? [];
    $backUrl = $devisBackUrl ?? route('app.'.$profileSlug.'.devis');
    $formatDate = function ($iso) {
        if (empty($iso)) {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($iso)->locale('fr')->translatedFormat('d M Y, H:i');
        } catch (\Throwable) {
            return (string) $iso;
        }
    };
    $kindClass = match ((string) ($d['subject_kind'] ?? '')) {
        'commande_produits' => 'devis-kind--products',
        'besoin_opportunite' => 'devis-kind--besoin',
        'service' => 'devis-kind--service',
        'brouillon_interne' => 'devis-kind--draft',
        default => 'devis-kind--chantier',
    };
    $isSupplierOrder = ! empty($d['is_supplier_received_order']);
    $status = (string) ($d['status'] ?? '');
    $canActAsProvider = ! empty($devisCanManage) && ! in_array($status, ['valide', 'rejete'], true);
    $clientUserId = (int) ($d['client_user_id'] ?? 0);
    $messagesUrl = $clientUserId > 0
        ? route('app.'.$profileSlug.'.messages', ['peer_id' => $clientUserId])
        : null;
    $clientName = (string) ($d['client_name'] ?? 'Client');
    $clientInitials = \Illuminate\Support\Str::of($clientName)
        ->trim()->explode(' ')->filter()->take(2)
        ->map(fn (string $w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
    if ($clientInitials === '') {
        $clientInitials = 'C';
    }
    $li = is_array($d['line_items'] ?? null) ? $d['line_items'] : [];
    $totals = is_array($li['totals'] ?? null) ? $li['totals'] : [];
    $subtotal = (int) ($totals['subtotal_fcfa'] ?? 0);
    $discountPct = (int) ($li['discount_pct'] ?? $li['remise_pct'] ?? 0);
    $tvaPct = (int) ($li['tva_pct'] ?? 0);
    $discountFcfa = (int) ($totals['discount_fcfa'] ?? 0);
    $tvaFcfa = (int) ($totals['tva_fcfa'] ?? 0);
    $totalFcfa = (int) ($totals['total_fcfa'] ?? 0);
    $quoteUrl = request()->url().'?quote=1&from='.e(request('from')).'&direction='.e(request('direction'));
@endphp

<div class="app-card app-card--flush app-flex-between-wrap app-mb-sm">
    <a href="{{ $backUrl }}" class="app-text-link">← Retour à la liste</a>
</div>

@if (! empty($d))
    <div class="order-layout">
        <aside class="order-layout__sidebar" aria-label="Résumé commande">
            <div class="order-card order-card--status">
                <h2 class="order-card__title">Statut</h2>
                <span class="order-status-pill">{{ $d['status_label'] ?? $status }}</span>
                <div class="order-client-mini">
                    <span class="order-client-mini__avatar" aria-hidden="true">{{ $clientInitials }}</span>
                    <div>
                        <div class="order-client-mini__name">{{ $clientName }}</div>
                        <div class="order-client-mini__role">Client</div>
                    </div>
                </div>
            </div>

            @if (! empty($totals) || $subtotal > 0 || $totalFcfa > 0)
                <div class="order-card order-card--finance">
                    <h2 class="order-card__title">Résumé financier</h2>
                    <div class="order-finance">
                        <div class="order-finance__row">
                            <span>Sous-total</span>
                            <span class="order-finance__val">{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="order-finance__row">
                            <span>Remise ({{ $discountPct }} %)</span>
                            <span class="order-finance__val">− {{ number_format($discountFcfa, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="order-finance__row">
                            <span>TVA ({{ $tvaPct }} %)</span>
                            <span class="order-finance__val">{{ number_format($tvaFcfa, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="order-finance__row order-finance__row--total">
                            <span>Total</span>
                            <span class="order-finance__val">{{ number_format($totalFcfa, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                    @if ($canActAsProvider && $isSupplierOrder)
                        @if (! empty($devisShowQuote))
                            <a href="#devis-quote" class="order-btn-send">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                Envoyer le devis
                            </a>
                        @else
                            <a href="{{ $quoteUrl }}" class="order-btn-send">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                {{ ! empty($d['has_provider_response']) ? 'Modifier le devis' : 'Envoyer le devis' }}
                            </a>
                        @endif
                    @endif
                </div>
            @endif

            @if (! empty($d['notes']))
                <div class="order-card order-card--notes">
                    <h2 class="order-card__title">Notes</h2>
                    <p class="app-readable app-mb-0">{{ $d['notes'] }}</p>
                </div>
            @endif

            @if ($canActAsProvider)
                <div class="order-card order-card--actions">
                    <h2 class="order-card__title">Actions</h2>
                    @if ($errors->has('devis_update'))
                        <div class="app-alert app-alert--error app-mb-sm" role="alert">{{ $errors->first('devis_update') }}</div>
                    @endif
                    <div class="devis-actions-stack">
                        @if ($isSupplierOrder)
                            @if (! empty($devisShowQuote))
                                <a href="{{ request()->url() }}?quote=0&from={{ request('from') }}&direction={{ request('direction') }}" class="app-btn app-btn--secondary app-btn--inline app-btn--block-sm">Masquer le formulaire</a>
                            @endif
                            <form method="post" action="{{ route('app.'.$profileSlug.'.devis.update', ['devis' => $d['id'] ?? 0]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="action" value="validate">
                                <button type="submit" class="app-btn app-btn--inline app-btn--block-sm" @disabled(! empty($d['needs_supplier_quote']))>Valider la commande</button>
                            </form>
                            <form method="post" action="{{ route('app.'.$profileSlug.'.devis.update', ['devis' => $d['id'] ?? 0]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="app-btn app-btn--ghost app-btn--inline app-btn--block-sm">Refuser</button>
                            </form>
                        @else
                            @if (! empty($devisShowQuote))
                                <a href="{{ request()->url() }}?quote=0&from={{ request('from') }}&direction={{ request('direction') }}" class="app-btn app-btn--secondary app-btn--inline app-btn--block-sm">Masquer le formulaire</a>
                            @else
                                <a href="{{ $quoteUrl }}" class="app-btn app-btn--inline app-btn--block-sm">Faire le devis</a>
                            @endif
                            <form method="post" action="{{ route('app.'.$profileSlug.'.devis.update', ['devis' => $d['id'] ?? 0]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="app-btn app-btn--ghost app-btn--inline app-btn--block-sm">Refuser</button>
                            </form>
                        @endif
                        @if ($messagesUrl)
                            <a href="{{ $messagesUrl }}" class="app-btn app-btn--secondary app-btn--inline app-btn--block-sm">Contacter le client</a>
                        @endif
                    </div>
                    @if ($isSupplierOrder && ! empty($d['needs_supplier_quote']))
                        <p class="app-muted app-text-sm app-mt-sm">Établissez le devis avant de valider la commande.</p>
                    @endif
                </div>
            @endif

            @if (! empty($devisCanRespondAsClient))
                <div class="order-card order-card--actions">
                    <h2 class="order-card__title">Votre réponse</h2>
                    @if ($errors->has('devis_update'))
                        <div class="app-alert app-alert--error app-mb-sm" role="alert">{{ $errors->first('devis_update') }}</div>
                    @endif
                    <div class="devis-actions-stack">
                        <form method="post" action="{{ route('app.'.$profileSlug.'.devis.update', ['devis' => $d['id'] ?? 0]) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="valide">
                            <button type="submit" class="app-btn app-btn--inline app-btn--block-sm">Accepter le devis</button>
                        </form>
                        <form method="post" action="{{ route('app.'.$profileSlug.'.devis.update', ['devis' => $d['id'] ?? 0]) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="rejete">
                            <button type="submit" class="app-btn app-btn--ghost app-btn--inline app-btn--block-sm">Refuser</button>
                        </form>
                    </div>
                </div>
            @endif
        </aside>

        <div class="order-layout__main">
            <div class="order-card order-card--client">
                <div class="order-card__head">
                    <span class="order-card__head-icon" aria-hidden="true">@include('app.partials.app-nav-icon', ['name' => 'user'])</span>
                    <h2 class="order-card__title app-mb-0">Informations client</h2>
                </div>
                <ul class="order-info-list">
                    <li><span>Lieu</span><strong>{{ $d['place'] ?? '—' }}</strong></li>
                    <li><span>Téléphone</span><strong>{{ $d['client_phone'] ?? $d['contact'] ?? '—' }}</strong></li>
                    <li><span>E-mail</span><strong>{{ $d['client_email'] ?? '—' }}</strong></li>
                    <li><span>Réf. commande</span><strong>{{ $d['order_reference'] ?? '—' }}</strong></li>
                    <li><span>Créé le</span><strong>{{ $formatDate($d['created_at'] ?? null) }}</strong></li>
                    @if (! empty($d['prestataire_name']))
                        <li><span>Prestataire</span><strong>{{ $d['prestataire_name'] }}</strong></li>
                    @endif
                </ul>
            </div>

            <div class="order-card app-mb-sm">
                <div class="devis-detail__head app-flex-between-wrap app-gap-sm">
                    <div>
                        <span class="devis-kind {{ $kindClass }}">{{ $d['subject_kind_label'] ?? 'Devis' }}</span>
                        @if (! empty($d['is_marketplace_request']))
                            <span class="app-pill app-ml-xs">Marketplace</span>
                        @endif
                    </div>
                </div>
                <h2 class="app-section-title app-mb-0">{{ $d['title'] ?? 'Devis #'.$d['id'] }}</h2>
            </div>

            @if (! empty($devisShowQuote) && ! empty($devisCanManage))
                @include('app.shell.partials.devis_quote')
            @elseif (! empty($d['line_items']))
                <div class="order-card">
                    <h2 class="order-card__title">Détail du montant</h2>
                    @if (! empty($li['lignes']) && is_array($li['lignes']))
                        <div class="devis-quote-lines">
                            <div class="devis-quote-lines__head">
                                <span>Description</span>
                                <span>Qté</span>
                                <span>Montant</span>
                                <span></span>
                            </div>
                            @foreach ($li['lignes'] as $line)
                                @if (is_array($line))
                                    <div class="devis-quote-lines__row">
                                        <span>{{ $line['label'] ?? $line['description'] ?? $line['title'] ?? '—' }}</span>
                                        <span>{{ $line['qty'] ?? $line['quantity'] ?? '—' }}</span>
                                        <span>
                                            @if (isset($line['line_total_fcfa']))
                                                {{ number_format((int) $line['line_total_fcfa'], 0, ',', ' ') }} FCFA
                                            @elseif (isset($line['total']))
                                                {{ number_format((int) $line['total'], 0, ',', ' ') }} FCFA
                                            @else
                                                —
                                            @endif
                                        </span>
                                        <span></span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @elseif (! empty($li['order_lignes']) && is_array($li['order_lignes']))
                        <div class="devis-quote-lines">
                            @foreach ($li['order_lignes'] as $line)
                                @if (is_array($line))
                                    <div class="devis-quote-lines__row">
                                        <span>{{ $line['label'] ?? $line['title'] ?? '—' }}</span>
                                        <span>{{ $line['qty'] ?? $line['quantity'] ?? '—' }}</span>
                                        <span>
                                            @php $tot = $line['line_total_fcfa'] ?? $line['total'] ?? null; @endphp
                                            {{ $tot !== null ? number_format((int) $tot, 0, ',', ' ').' FCFA' : 'Sur devis' }}
                                        </span>
                                        <span></span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="app-muted app-mb-0">Le détail des lignes n’est pas disponible pour ce devis.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
