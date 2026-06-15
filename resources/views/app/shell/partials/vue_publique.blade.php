@php
    $u = $profileData['user'] ?? [];
    $displayName = trim((string) ($u['display_name'] ?? ''));
    if ($displayName === '') {
        $company = trim((string) ($u['company_name'] ?? ''));
        $displayName = $company !== '' ? $company : (string) ($u['name'] ?? 'Entreprise');
    }
    $bio = trim((string) ($u['bio'] ?? ''));
    if ($bio === '') {
        $bio = trim((string) ($u['company_description'] ?? ''));
    }
@endphp

<div class="app-card app-mt app-vue-publique-head">
    <h2 class="app-section-title">{{ $displayName }}</h2>
    @if ($bio !== '')
        <p class="app-muted app-mb-0">{{ $bio }}</p>
    @endif
</div>

<div class="app-card app-mt">
    <h3 class="app-section-title">Catalogue</h3>
    @if (! empty($productsList) && count($productsList))
        <div class="app-supplier-home-grid">
            @foreach ($productsList as $p)
                <article class="app-supplier-home-tile">
                    <span class="app-supplier-home-tile__badge">{{ (int) ($p['views_count'] ?? 0) }} vues</span>
                    <div class="app-supplier-home-tile__img-wrap">
                        @if (! empty($p['image_url']))
                            <img src="{{ $p['image_url'] }}" alt="" class="app-supplier-home-tile__img">
                        @else
                            <div class="app-supplier-home-tile__img-ph" aria-hidden="true"></div>
                        @endif
                    </div>
                    <h4 class="app-supplier-home-tile__title">{{ $p['title'] ?? '—' }}</h4>
                    <p class="app-supplier-home-tile__price">{{ $p['price_display_fr'] ?? '—' }}</p>
                    <p class="app-supplier-home-tile__stock">Stock : {{ (int) ($p['stock_units'] ?? 0) }}</p>
                </article>
            @endforeach
        </div>
    @else
        <p class="app-muted app-mb-0">Aucun produit publié dans votre catalogue pour l’instant.</p>
    @endif
</div>
