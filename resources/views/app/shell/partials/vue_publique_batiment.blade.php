@php
    $u = $profileData['user'] ?? [];
    $company = trim((string) ($u['company_name'] ?? ''));
    $displayName = $company !== '' ? $company : (string) ($u['name'] ?? 'Entreprise');
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
    <h3 class="app-section-title">Besoins &amp; chantiers publiés</h3>
    @if (! empty($previewBesoins) && count($previewBesoins))
        <ul class="app-vue-btp-list">
            @foreach ($previewBesoins as $b)
                <li class="app-vue-btp-list__item">
                    <strong>{{ $b['title'] ?? '—' }}</strong>
                    <span class="app-muted">
                        {{ $b['start_label'] ?? ($b['short_date'] ?? '—') }}
                        · {{ $b['place'] ?? '—' }}
                        · {{ $b['budget'] ?? '—' }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="app-muted app-mb-0">Aucun besoin publié pour l’instant.</p>
    @endif
</div>

<div class="app-card app-mt">
    <h3 class="app-section-title">Prestations entreprise</h3>
    @if (! empty($previewServices) && count($previewServices))
        <div class="app-supplier-home-grid">
            @foreach ($previewServices as $s)
                <article class="app-supplier-home-tile">
                    <div class="app-supplier-home-tile__img-wrap">
                        @if (! empty($s['image_url']))
                            <img src="{{ $s['image_url'] }}" alt="" class="app-supplier-home-tile__img">
                        @else
                            <div class="app-supplier-home-tile__img-ph" aria-hidden="true"></div>
                        @endif
                    </div>
                    <h4 class="app-supplier-home-tile__title">{{ $s['title'] ?? '—' }}</h4>
                    <p class="app-supplier-home-tile__price app-muted">{{ $s['category']['name'] ?? '' }}</p>
                    <p class="app-supplier-home-tile__stock">{{ $s['location'] ?? '—' }}</p>
                </article>
            @endforeach
        </div>
    @else
        <p class="app-muted app-mb-0">Aucune prestation publiée pour l’instant.</p>
    @endif
</div>
