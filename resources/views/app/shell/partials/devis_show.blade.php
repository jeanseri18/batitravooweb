@php
    $d = $devisDetail['data'] ?? [];
@endphp
<div class="app-card app-card--flush app-flex-between-wrap">
    <a href="{{ route('app.'.$profileSlug.'.devis') }}" class="app-text-link">← Retour à la liste</a>
</div>

<!-- Formater la date -->
@php
    $formatDate = function ($iso) {
        if (empty($iso)) return '—';
        try {
            $c = \Carbon\Carbon::parse($iso)->locale('fr');
            return $c->translatedFormat('d M Y, H:i');
        } catch (\Throwable) { return (string) $iso; }
    };
@endphp

@if (! empty($d))
    <div class="app-card app-mt">
        <h2 class="app-section-title">{{ $d['title'] ?? 'Devis #'.$d['id'] }}</h2>
        <p class="app-muted">Statut : <span class="app-pill">{{ $d['status_label'] ?? ($d['status'] ?? '—') }}</span></p>
        <table class="app-table app-table--bordered app-mt">
            <tbody>
                <tr><th>ID</th><td>#{{ $d['id'] ?? '—' }}</td></tr>
                <tr><th>Client</th><td>{{ $d['client_name'] ?? '—' }}</td></tr>
                <tr><th>Réf. commande</th><td>{{ $d['order_reference'] ?? '—' }}</td></tr>
                <tr><th>Lieu</th><td>{{ $d['place'] ?? '—' }}</td></tr>
                <tr><th>Contact</th><td>{{ $d['contact'] ?? '—' }}</td></tr>
                <tr><th>Créé le</th><td>{{ $formatDate($d['created_at'] ?? '—') }}</td></tr>
            </tbody>
        </table>
        @if (! empty($d['line_items']))
            <h3 class="app-section-title app-mt">Détail du montant</h3>
            @php $li = $d['line_items']; @endphp
            @if (! empty($li['lignes']) && is_array($li['lignes']))
                <div class="app-table-wrap">
                    <table class="app-table app-table--bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Qté</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($li['lignes'] as $line)
                                @if (is_array($line))
                                    <tr>
                                        <td>{{ $line['label'] ?? $line['description'] ?? $line['title'] ?? '—' }}</td>
                                        <td>{{ $line['qty'] ?? $line['quantity'] ?? '—' }}</td>
                                        <td>
                                            @if (isset($line['line_total_fcfa']))
                                                {{ number_format((int) $line['line_total_fcfa'], 0, ',', ' ') }} FCFA
                                            @elseif (isset($line['total']))
                                                {{ number_format((int) $line['total'], 0, ',', ' ') }} FCFA
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="app-muted">Le détail des lignes n’est pas disponible au format tableau pour ce devis.</p>
            @endif
            @if (! empty($li['totals']) && is_array($li['totals']))
                @php $tot = $li['totals']; @endphp
                <dl class="app-profile-dl app-mt-md">
                    @if (isset($tot['subtotal_fcfa']))
                        <div class="app-profile-dl__row">
                            <dt>Sous-total</dt>
                            <dd>{{ number_format((int) $tot['subtotal_fcfa'], 0, ',', ' ') }} FCFA</dd>
                        </div>
                    @endif
                    @if (! empty($tot['discount_fcfa']) && (int) $tot['discount_fcfa'] > 0)
                        <div class="app-profile-dl__row">
                            <dt>Remise @if (! empty($li['discount_pct'])) ({{ (int) $li['discount_pct'] }} %) @endif</dt>
                            <dd>− {{ number_format((int) $tot['discount_fcfa'], 0, ',', ' ') }} FCFA</dd>
                        </div>
                    @endif
                    @if (! empty($tot['discount_fcfa']) && (int) $tot['discount_fcfa'] > 0 && isset($tot['subtotal_after_discount_fcfa']))
                        <div class="app-profile-dl__row">
                            <dt>Après remise</dt>
                            <dd>{{ number_format((int) $tot['subtotal_after_discount_fcfa'], 0, ',', ' ') }} FCFA</dd>
                        </div>
                    @endif
                    @if (! empty($tot['tva_fcfa']) && (int) $tot['tva_fcfa'] > 0)
                        <div class="app-profile-dl__row">
                            <dt>TVA @if (! empty($li['tva_pct'])) ({{ (int) $li['tva_pct'] }} %) @endif</dt>
                            <dd>{{ number_format((int) $tot['tva_fcfa'], 0, ',', ' ') }} FCFA</dd>
                        </div>
                    @endif
                    @if (! empty($tot['total_fcfa']))
                        <div class="app-profile-dl__row">
                            <dt><strong>Total TTC</strong></dt>
                            <dd><strong>{{ number_format((int) $tot['total_fcfa'], 0, ',', ' ') }} FCFA</strong></dd>
                        </div>
                    @elseif (! empty($tot['subtotal_fcfa']))
                        <div class="app-profile-dl__row">
                            <dt><strong>Total</strong></dt>
                            <dd><strong>{{ number_format((int) $tot['subtotal_fcfa'], 0, ',', ' ') }} FCFA</strong></dd>
                        </div>
                    @endif
                </dl>
            @endif
        @endif
        @if (! empty($d['notes']))
            <h3 class="app-section-title app-mt">Notes</h3>
            <p class="app-readable">{{ $d['notes'] }}</p>
        @endif

        @if (! empty($devisCanManage))
            <div class="app-dashboard-panel__body app-mt" style="padding-top:1rem;border-top:1px solid var(--border);">
                <h3 class="app-section-title">Mettre à jour (prestataire)</h3>
                @if ($errors->has('devis_update'))
                    <div class="app-alert app-alert--error app-mb-md" role="alert">{{ $errors->first('devis_update') }}</div>
                @endif
                <form method="post" action="{{ route('app.'.$profileSlug.'.devis.update', ['devis' => $d['id'] ?? 0]) }}" class="app-form-stack">
                    @csrf
                    @method('PUT')
                    <div class="app-field">
                        <label for="du-status">Statut</label>
                        <select name="status" id="du-status">
                            <option value="">— inchangé —</option>
                            @foreach ([
                                'non_traite' => 'Non traité',
                                'en_cours' => 'En cours',
                                'envoye' => 'Envoyé',
                                'valide' => 'Validé',
                                'rejete' => 'Rejeté',
                            ] as $code => $lbl)
                                <option value="{{ $code }}" @selected(old('status', $d['status'] ?? '') === $code)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="app-field">
                        <label for="du-ref">Référence commande</label>
                        <input type="text" name="order_reference" id="du-ref" maxlength="64" value="{{ old('order_reference', $d['order_reference'] ?? '') }}">
                    </div>
                    <div class="app-field">
                        <label for="du-notes">Notes internes / précisions</label>
                        <textarea name="notes" id="du-notes" rows="4" maxlength="10000">{{ old('notes', $d['notes'] ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="app-btn app-btn--inline">Enregistrer</button>
                </form>
            </div>
        @endif
    </div>
@endif
