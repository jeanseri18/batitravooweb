@php
    $u = ($profileData ?? [])['user'] ?? [];
    $defaultClient = $u['name'] ?? '';
    $ownerId = (int) ($devisOwnerUserId ?? 0);
    $isParticulierRequest = $profileSlug === 'particulier';
    $defaultTitle = trim((string) ($devisRequestTitle ?? ''));
    if ($defaultTitle === '' && $isParticulierRequest) {
        $defaultTitle = 'Demande marketplace';
    }
@endphp

@if ($ownerId <= 0 && ! $isParticulierRequest)
    <div class="app-alert app-alert--warn app-mb-md" role="status">
        Ouvrez cette page depuis une annonce (produit ou service) avec le bouton « Demander un devis », ou ajoutez <code class="app-muted">?owner_user_id=…</code> à l’URL avec l’identifiant du prestataire.
    </div>
@endif

@if ($isParticulierRequest)
    <p class="app-muted app-mb-md">Décrivez votre besoin : le prestataire vous répondra avec un devis chiffré. Vous ne créez pas de devis vous-même.</p>
@elseif ($profileSlug === 'fournisseur')
    <p class="app-muted app-mb-md">Structure proche de l’éditeur mobile : client, lignes (désignation, quantité, prix unitaire), remise et TVA optionnelles. Les lignes sont jointes à la demande sous forme de tableau récapitulatif.</p>
@endif

<form method="post" action="{{ route('app.'.$profileSlug.'.devis.store') }}" class="app-card app-form-stack" id="devis-create-form">
    @csrf
    <input type="hidden" name="owner_user_id" value="{{ old('owner_user_id', $ownerId) }}">
    <input type="hidden" name="client_name" value="{{ old('client_name', $defaultClient) }}">

    @if ($errors->has('devis_store'))
        <div class="app-alert app-alert--error" role="alert">{{ $errors->first('devis_store') }}</div>
    @endif
    @error('owner_user_id')
        <div class="app-alert app-alert--error" role="alert">{{ $message }}</div>
    @enderror

    @if ($isParticulierRequest)
        <div class="app-field">
            <label for="dv-title">Objet de la demande <span class="app-muted">*</span></label>
            <input type="text" name="title" id="dv-title" required maxlength="255" value="{{ old('title', $defaultTitle) }}" autocomplete="off" placeholder="Ex. Rénovation salle de bain">
            @error('title')
                <span class="app-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="app-field">
            <label for="dv-notes">Précisez votre besoin</label>
            <textarea name="notes" id="dv-notes" rows="5" maxlength="10000" placeholder="Décrivez les travaux, le délai souhaité, etc.">{{ old('notes') }}</textarea>
        </div>

        <div class="app-field">
            <label for="dv-place">Adresse ou lieu du chantier</label>
            <input type="text" name="place" id="dv-place" maxlength="255" value="{{ old('place') }}" placeholder="Ex. Quartier, ville…">
        </div>

        <div class="app-field">
            <label for="dv-contact">Téléphone de contact</label>
            <input type="text" name="contact" id="dv-contact" maxlength="255" value="{{ old('contact', $u['phone'] ?? '') }}" placeholder="Téléphone">
        </div>

        <button type="submit" class="app-btn app-btn--inline">Envoyer la demande</button>
    @else
        <div class="app-field">
            <label for="dv-owner">ID prestataire concerné <span class="app-muted">*</span></label>
            <input type="number" name="owner_user_id" id="dv-owner" required min="1" step="1" value="{{ old('owner_user_id', $ownerId) }}">
            @error('owner_user_id')
                <span class="app-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="app-field">
            <label for="dv-title">Objet <span class="app-muted">*</span></label>
            <input type="text" name="title" id="dv-title" required maxlength="255" value="{{ old('title', $defaultTitle) }}" autocomplete="off">
            @error('title')
                <span class="app-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="app-field">
            <label for="dv-client">Nom du client (tel qu’affiché au prestataire) <span class="app-muted">*</span></label>
            <input type="text" name="client_name" id="dv-client" required maxlength="255" value="{{ old('client_name', $defaultClient) }}" autocomplete="name">
            @error('client_name')
                <span class="app-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="app-field">
            <label for="dv-ref">Référence commande</label>
            <input type="text" name="order_reference" id="dv-ref" maxlength="64" value="{{ old('order_reference') }}">
            @error('order_reference')
                <span class="app-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="app-field">
            <label for="dv-place">Adresse ou lieu</label>
            <input type="text" name="place" id="dv-place" maxlength="255" value="{{ old('place') }}" placeholder="Ex. Quartier, ville…">
        </div>

        <div class="app-field">
            <label for="dv-contact">Téléphone</label>
            <input type="text" name="contact" id="dv-contact" maxlength="255" value="{{ old('contact', $u['phone'] ?? '') }}" placeholder="Téléphone">
        </div>

        <div class="app-field">
            <label for="dv-client-email">E-mail du client</label>
            <input type="email" name="client_email" id="dv-client-email" maxlength="255" value="{{ old('client_email', $u['email'] ?? '') }}" autocomplete="email">
            <span class="app-field-hint">Ajouté en bas des notes pour le prestataire si renseigné.</span>
        </div>

        <div class="app-field">
            <label for="dv-notes">Message ou précisions</label>
            <textarea name="notes" id="dv-notes" rows="4" maxlength="10000">{{ old('notes') }}</textarea>
        </div>

        <div class="app-card app-card--flush app-mt app-devis-lines-card">
            <h3 class="app-section-title app-section-title--flush">Lignes de proposition <span class="app-muted app-text-sm">(optionnel)</span></h3>
            <p class="app-muted app-text-sm app-mb-md">Renseignez au moins la désignation pour chaque ligne à inclure dans le PDF / récap côté prestataire.</p>
            <div class="app-table-wrap">
                <table class="app-table app-table--bordered" id="devis-lines-table">
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Qté</th>
                            <th>Prix unitaire (FCFA)</th>
                            <th aria-hidden="true"></th>
                        </tr>
                    </thead>
                    <tbody id="devis-lines-body">
                        @foreach (range(0, 2) as $rowIdx)
                            <tr class="devis-line-row">
                                <td><input type="text" name="line_label[]" class="app-input" maxlength="255" value="{{ old('line_label.'.$rowIdx) }}" placeholder="Article ou prestation"></td>
                                <td><input type="number" name="line_qty[]" class="app-input" min="1" value="{{ old('line_qty.'.$rowIdx, '1') }}"></td>
                                <td><input type="number" name="line_unit_fcfa[]" class="app-input" min="0" step="1" value="{{ old('line_unit_fcfa.'.$rowIdx, '0') }}"></td>
                                <td><button type="button" class="app-text-link app-text-link--danger devis-line-remove" tabindex="-1">Retirer</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="app-mt-sm app-mb-0">
                <button type="button" class="app-btn app-btn--ghost app-btn--sm" id="devis-line-add">+ Ajouter une ligne</button>
            </p>
            <div class="app-form-grid-profile app-mt-md">
                <div class="app-field">
                    <label for="dv-discount">Remise (%)</label>
                    <input type="number" name="discount_pct" id="dv-discount" min="0" max="100" value="{{ old('discount_pct', '0') }}">
                </div>
                <div class="app-field">
                    <label for="dv-tva">TVA (%)</label>
                    <input type="number" name="tva_pct" id="dv-tva" min="0" max="100" value="{{ old('tva_pct', '0') }}">
                </div>
            </div>
        </div>

        <button type="submit" class="app-btn app-btn--inline">Envoyer la demande</button>
    @endif
</form>

@if (! $isParticulierRequest)
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var body = document.getElementById('devis-lines-body');
        var addBtn = document.getElementById('devis-line-add');
        if (!body || !addBtn) return;

        function bindRemove(row) {
            var btn = row.querySelector('.devis-line-remove');
            if (!btn) return;
            btn.addEventListener('click', function () {
                if (body.querySelectorAll('.devis-line-row').length <= 1) return;
                row.remove();
            });
        }

        body.querySelectorAll('.devis-line-row').forEach(bindRemove);

        addBtn.addEventListener('click', function () {
            var first = body.querySelector('.devis-line-row');
            if (!first) return;
            var clone = first.cloneNode(true);
            clone.querySelectorAll('input').forEach(function (inp) {
                inp.value = inp.name.indexOf('line_qty') !== -1 ? '1' : inp.name.indexOf('line_unit_fcfa') !== -1 ? '0' : '';
            });
            body.appendChild(clone);
            bindRemove(clone);
        });
    });
    </script>
    @endpush
@endif
