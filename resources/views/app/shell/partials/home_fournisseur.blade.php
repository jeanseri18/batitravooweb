@if ($profileSlug === 'fournisseur')
    @include('app.shell.partials.home_hero', [
        'heroCtaLabel' => '+ Ajouter un produit',
        'heroCtaUrl' => route('app.fournisseur.products.create'),
    ])

    @if (! empty($dashboard['kpis']))
        @include('app.shell.partials.dashboard_metrics')
    @endif

    @include('app.shell.partials.home_search', [
        'searchLabel' => 'Recherche dans mes produits',
        'searchPlaceholder' => 'Filtrer par titre…',
        'searchId' => 'supplier-home-search',
        'searchLocal' => true,
    ])

    @include('app.shell.partials.home_shortcuts')

    <section class="app-home-block app-home-block--products" aria-labelledby="supplier-home-products-title">
        @include('app.shell.partials.home_section_head', [
            'title' => 'Produits récemment publiés',
            'scrollTarget' => '#supplier-home-grid',
            'sectionId' => 'supplier-home-products-title',
        ])
        <div class="app-card app-home-products app-home-section">
        <p class="app-home-block__actions app-mb-sm">
            <a href="{{ route('app.fournisseur.products') }}" class="app-text-link">Voir tout le catalogue</a>
        </p>
        @if (empty($supplierProducts) || ! count($supplierProducts))
            <p class="app-muted app-mt-sm">Aucun produit en base pour le moment. Ajoutez des articles depuis « Catalogue produits ».</p>
        @else
            <div id="supplier-home-grid" class="app-supplier-home-grid app-home-carousel app-mt-sm">
                @foreach ($supplierProducts as $p)
                    @php
                        $title = (string) ($p['title'] ?? '');
                        $slugTitle = \Illuminate\Support\Str::lower($title);
                    @endphp
                    <article class="app-supplier-home-tile" data-title-search="{{ e($slugTitle) }}">
                        <div class="app-supplier-home-tile__img-wrap">
                            <span class="app-supplier-home-tile__badge">{{ (int) ($p['views_count'] ?? 0) }} vues</span>
                            @if (! empty($p['image_url']))
                                <img src="{{ $p['image_url'] }}" alt="" class="app-supplier-home-tile__img">
                            @else
                                <div class="app-supplier-home-tile__img-ph" aria-hidden="true"></div>
                            @endif
                        </div>
                        <h3 class="app-supplier-home-tile__title">{{ $title ?: '—' }}</h3>
                        @if (! empty($p['price_display_fr']))
                            <p class="app-supplier-home-tile__price">{{ $p['price_display_fr'] }}</p>
                        @endif
                        <p class="app-supplier-home-tile__stock {{ (int) ($p['stock_units'] ?? 0) > 0 ? 'is-in-stock' : '' }}">
                            {{ (int) ($p['stock_units'] ?? 0) > 0 ? 'En stock' : 'Rupture' }}
                        </p>
                    </article>
                @endforeach
            </div>
            <p id="supplier-home-empty" class="app-muted app-mt-md" hidden>Aucun produit ne correspond à votre recherche.</p>
        @endif
        </div>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('supplier-home-search');
            var grid = document.getElementById('supplier-home-grid');
            var empty = document.getElementById('supplier-home-empty');
            if (!input || !grid) return;
            var tiles = grid.querySelectorAll('[data-title-search]');
            function run() {
                var q = (input.value || '').trim().toLowerCase();
                var n = 0;
                tiles.forEach(function (el) {
                    var t = (el.getAttribute('data-title-search') || '').toLowerCase();
                    var show = !q || t.indexOf(q) !== -1;
                    el.hidden = !show;
                    if (show) n++;
                });
                if (empty) empty.hidden = n !== 0;
            }
            input.addEventListener('input', run);
            run();
        });
    </script>
    @endpush
@endif
