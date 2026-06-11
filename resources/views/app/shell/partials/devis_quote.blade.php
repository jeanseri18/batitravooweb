@php
    $d = $devisDetail['data'] ?? [];
    $builder = app(\App\Services\Web\ManualDevisQuoteBuilder::class);
    $formLines = $builder->extractFormLines($d['line_items'] ?? null);
    $li = is_array($d['line_items'] ?? null) ? $d['line_items'] : [];
    $discountPct = (int) ($li['discount_pct'] ?? $li['remise_pct'] ?? 0);
    $tvaPct = (int) ($li['tva_pct'] ?? 0);
    $hasResponse = ! empty($d['has_provider_response']);

    $oldLabels = old('line_label');
    if (is_array($oldLabels) && count($oldLabels) > 0) {
        $formLines = [];
        $oldQtys = old('line_qty', []);
        $oldUnits = old('line_unit_fcfa', []);
        foreach ($oldLabels as $i => $lbl) {
            $formLines[] = [
                'label' => (string) $lbl,
                'qty' => max(1, (int) ($oldQtys[$i] ?? 1)),
                'unit' => max(0, (int) ($oldUnits[$i] ?? 0)),
            ];
        }
    }
@endphp

<div class="order-card devis-quote-form" id="devis-quote">
    <div class="order-card__head">
        <span class="order-card__head-icon" aria-hidden="true">@include('app.partials.app-nav-icon', ['name' => 'document'])</span>
        <div>
            <h2 class="order-card__title app-mb-0">{{ $hasResponse ? 'Modifier le devis' : 'Faire le devis' }}</h2>
            <p class="app-muted app-text-sm app-mb-0 app-mt-xs">Saisissez les lignes et montants, puis envoyez la proposition au client.</p>
        </div>
    </div>

    @if ($errors->has('devis_update'))
        <div class="app-alert app-alert--error app-mb-md" role="alert">{{ $errors->first('devis_update') }}</div>
    @endif

    <form method="post" action="{{ route('app.'.$profileSlug.'.devis.update', ['devis' => $d['id'] ?? 0]) }}" class="app-form-stack app-mt-md" id="devis-quote-form">
        @csrf
        @method('PUT')

        <div class="devis-quote-lines" id="devis-quote-lines">
            <div class="devis-quote-lines__head">
                <span>Description</span>
                <span>Qté</span>
                <span>Prix unitaire</span>
                <span class="devis-quote-lines__head-actions" aria-hidden="true"></span>
            </div>
            <div class="devis-quote-lines__body" id="devis-quote-lines-body">
                @foreach ($formLines as $i => $line)
                    <div class="devis-quote-lines__row devis-quote-line-row">
                        <input type="text" name="line_label[]" value="{{ $line['label'] }}" placeholder="Article ou prestation" maxlength="255" class="app-input">
                        <input type="number" name="line_qty[]" value="{{ $line['qty'] }}" min="1" class="app-input" aria-label="Quantité">
                        <input type="number" name="line_unit_fcfa[]" value="{{ $line['unit'] }}" min="0" class="app-input" aria-label="Prix unitaire FCFA">
                        <button type="button" class="devis-quote-line-remove" aria-label="Supprimer la ligne" title="Supprimer la ligne">×</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="devis-quote-lines__add" id="devis-quote-line-add">
                <span class="devis-quote-lines__add-icon" aria-hidden="true">+</span>
                Ajouter une ligne
            </button>
        </div>

        <div class="app-form-grid app-form-grid--2 app-mt-md">
            <div class="app-field app-mb-0">
                <label for="dq-discount">Remise (%)</label>
                <input type="number" name="discount_pct" id="dq-discount" min="0" max="100" value="{{ old('discount_pct', $discountPct) }}" class="app-input">
            </div>
            <div class="app-field app-mb-0">
                <label for="dq-tva">TVA (%)</label>
                <input type="number" name="tva_pct" id="dq-tva" min="0" max="100" value="{{ old('tva_pct', $tvaPct) }}" class="app-input">
            </div>
        </div>

        <button type="submit" name="action" value="quote_send" class="order-btn-send app-mt-md">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            Envoyer le devis
        </button>
        <button type="submit" name="action" value="quote_draft" class="app-btn app-btn--secondary app-btn--inline app-mt-sm devis-quote-draft-btn">Enregistrer brouillon</button>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var body = document.getElementById('devis-quote-lines-body');
    var addBtn = document.getElementById('devis-quote-line-add');
    if (!body || !addBtn) return;

    function updateRemoveButtons() {
        var rows = body.querySelectorAll('.devis-quote-line-row');
        var single = rows.length <= 1;
        rows.forEach(function (row) {
            var btn = row.querySelector('.devis-quote-line-remove');
            if (!btn) return;
            btn.disabled = single;
            btn.setAttribute('aria-disabled', single ? 'true' : 'false');
            btn.title = single ? 'Au moins une ligne est requise' : 'Supprimer la ligne';
        });
    }

    function bindRemove(row) {
        var btn = row.querySelector('.devis-quote-line-remove');
        if (!btn || btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', function () {
            if (body.querySelectorAll('.devis-quote-line-row').length <= 1) return;
            row.remove();
            updateRemoveButtons();
        });
    }

    function clearRow(row) {
        row.querySelectorAll('input').forEach(function (inp) {
            if (inp.name.indexOf('line_qty') !== -1) {
                inp.value = '1';
            } else if (inp.name.indexOf('line_unit_fcfa') !== -1) {
                inp.value = '0';
            } else {
                inp.value = '';
            }
        });
    }

    body.querySelectorAll('.devis-quote-line-row').forEach(function (row) {
        bindRemove(row);
    });
    updateRemoveButtons();

    addBtn.addEventListener('click', function () {
        var template = body.querySelector('.devis-quote-line-row');
        if (!template) return;
        var clone = template.cloneNode(true);
        clearRow(clone);
        clone.querySelectorAll('.devis-quote-line-remove').forEach(function (btn) {
            delete btn.dataset.bound;
        });
        body.appendChild(clone);
        bindRemove(clone);
        updateRemoveButtons();
        var firstInput = clone.querySelector('input[name="line_label[]"]');
        if (firstInput) firstInput.focus();
    });
});
</script>
@endpush
