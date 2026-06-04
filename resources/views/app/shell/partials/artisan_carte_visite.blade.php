@php
    /** @var \App\Models\ArtisanBusinessCard|null $businessCard */
    $card = $businessCard ?? null;
    $svcRaw = old('services', $card?->services ?? []);
    $svcList = is_array($svcRaw) ? array_values($svcRaw) : [];
    while (count($svcList) < 6) {
        $svcList[] = '';
    }
    $svcList = array_slice($svcList, 0, 6);
    $portfolioPaths = $card?->portfolio_paths ?? ($card?->portfolio_path ? [$card->portfolio_path] : []);
    if (! is_array($portfolioPaths)) {
        $portfolioPaths = [];
    }
    $priceMode = old('price_mode');
    if ($priceMode === null && $card) {
        if ($card->price_on_quote) {
            $priceMode = 'sur_devis';
        } elseif ($card->price_text && str_starts_with(strtolower($card->price_text), 'à partir de')) {
            $priceMode = 'variable';
        } else {
            $priceMode = 'fixe';
        }
    }
    $priceMode = $priceMode ?? 'fixe';
    $priceTextVal = old('price_text', $card?->price_text ?? '');
    if ($priceMode === 'variable' && $card?->price_text) {
        $priceTextVal = preg_replace('/^à partir de\s*/iu', '', (string) $card->price_text);
    }
@endphp

@if (count($portfolioPaths) > 0)
    <div class="app-card app-mt">
        <p class="app-muted app-mb-sm">Réalisations enregistrées ({{ count($portfolioPaths) }})</p>
        @foreach ($portfolioPaths as $p)
            @php $pfUrl = storage_public_url($p); @endphp
            @if ($pfUrl)
                <input type="hidden" name="keep_portfolio_paths[]" value="{{ $p }}">
                <p class="app-mb-sm"><a href="{{ $pfUrl }}" target="_blank" rel="noopener" class="app-text-link">Photo portfolio</a></p>
            @endif
        @endforeach
    </div>
@endif

<form method="post" action="{{ route('app.artisan.business_card.update') }}" enctype="multipart/form-data" class="app-card app-mt app-form-stack">
    @csrf

    <div class="app-field">
        <label for="bc-name">Nom affiché</label>
        <input type="text" name="display_name" id="bc-name" maxlength="255" value="{{ old('display_name', $card?->display_name) }}">
        @error('display_name')<span class="app-error">{{ $message }}</span>@enderror
    </div>

    <div class="app-field">
        <label for="bc-prof">Profession</label>
        <input type="text" name="profession" id="bc-prof" maxlength="255" value="{{ old('profession', $card?->profession) }}">
        @error('profession')<span class="app-error">{{ $message }}</span>@enderror
    </div>

    <div class="app-field">
        <label for="bc-exp">Expérience (court texte)</label>
        <input type="text" name="experience_text" id="bc-exp" maxlength="255" value="{{ old('experience_text', $card?->experience_text) }}">
        @error('experience_text')<span class="app-error">{{ $message }}</span>@enderror
    </div>

    <fieldset class="app-field">
        <legend class="app-muted app-text-sm app-mb-sm">Prix *</legend>
        <label class="app-checkbox-label app-mb-sm">
            <input type="radio" name="price_mode" value="fixe" @checked($priceMode === 'fixe')>
            Prix fixe
        </label>
        <label class="app-checkbox-label app-mb-sm">
            <input type="radio" name="price_mode" value="variable" @checked($priceMode === 'variable')>
            Prix variable
        </label>
        <label class="app-checkbox-label app-mb-sm">
            <input type="radio" name="price_mode" value="sur_devis" @checked($priceMode === 'sur_devis')>
            Prix sur devis
        </label>
        <label for="bc-price">Montant (fixe ou « à partir de »)</label>
        <input type="text" name="price_text" id="bc-price" maxlength="255" value="{{ $priceTextVal }}" placeholder="Ex. 300 000 FCFA">
        @error('price_text')<span class="app-error">{{ $message }}</span>@enderror
    </fieldset>

    <div class="app-field">
        <label class="app-muted app-text-sm">Prestations proposées (lignes)</label>
        @foreach ($svcList as $i => $line)
            <input type="text" name="services[]" class="app-mt-sm" style="display:block;width:100%;" maxlength="500" value="{{ $line }}" placeholder="Prestation {{ $i + 1 }}">
        @endforeach
        @error('services')<span class="app-error">{{ $message }}</span>@enderror
    </div>

    <fieldset class="app-field">
        <legend class="app-muted app-text-sm app-mb-sm">Disponibilité</legend>
        <label class="app-checkbox-label app-mb-sm">
            <input type="checkbox" name="avail_immediate" value="1" @checked(old('avail_immediate', $card?->avail_immediate))>
            Disponible immédiatement
        </label>
        <label class="app-checkbox-label app-mb-sm">
            <input type="checkbox" name="avail_appointment" value="1" @checked(old('avail_appointment', $card?->avail_appointment))>
            Sur rendez-vous
        </label>
        <label class="app-checkbox-label">
            <input type="checkbox" name="avail_unavailable" value="1" @checked(old('avail_unavailable', $card?->avail_unavailable))>
            Indisponible
        </label>
    </fieldset>

    <div class="app-field">
        <label for="bc-loc">Localisation / zone</label>
        <textarea name="location_text" id="bc-loc" rows="3" maxlength="500">{{ old('location_text', $card?->location_text) }}</textarea>
        @error('location_text')<span class="app-error">{{ $message }}</span>@enderror
    </div>

    <div class="app-field">
        <label for="bc-portfolio">Ajouter des réalisations (portfolio)</label>
        <p class="app-muted app-text-sm app-mb-sm">Une ou plusieurs photos (JPG, PNG, WebP — max 15 Mo chacune).</p>
        <input type="file" name="portfolio[]" id="bc-portfolio" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" multiple>
        @error('portfolio')<span class="app-error">{{ $message }}</span>@enderror
        @error('portfolio.*')<span class="app-error">{{ $message }}</span>@enderror
    </div>

    <div class="app-form-actions">
        <button type="submit" class="app-btn app-btn--inline">Enregistrer</button>
    </div>
</form>

@if ($card)
    <div class="app-card app-mt">
        <form method="post" action="{{ route('app.artisan.business_card.destroy') }}" onsubmit="return confirm('Supprimer toute la carte de visite ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="app-text-link app-text-link--danger">Supprimer la carte de visite</button>
        </form>
    </div>
@endif
