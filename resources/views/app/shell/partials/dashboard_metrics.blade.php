@php
    $dashFull = ($page ?? '') === 'dashboard_tab';
    $periodBase = $dashFull
        ? ($metricsPeriodRoute ?? route('app.'.$profileSlug.'.dashboard'))
        : route('app.'.$profileSlug.'.home');
    $dash = is_array($dashboard ?? null) ? $dashboard : [];
    $activePeriod = request('period', 'month');
    if (! in_array($activePeriod, ['week', 'month', 'year'], true)) {
        $activePeriod = 'month';
    }
@endphp
@if ($dashFull && ! empty($dash['kpis']))
    <h2 class="app-section-title app-mb-sm">Vue d’ensemble</h2>
@endif
@if (! empty($dash['kpis']))
@php $kpiIcons = ['chart', 'eye', 'cart', 'document', 'stack', 'bell']; @endphp
@if (! $dashFull)
<section class="app-home-block app-home-block--kpi" aria-labelledby="home-kpi-title">
    @include('app.shell.partials.home_section_head', [
        'title' => 'Indicateurs clés',
        'scrollTarget' => '#home-kpi-carousel',
        'sectionId' => 'home-kpi-title',
    ])
    <div class="app-card app-home-kpi app-home-section">
@endif
<div class="app-home-stats">
@if (! empty($dash['kpis']))
<div class="app-card app-card--flush app-metrics-period @if (! $dashFull) app-metrics-period--home @endif">
    <p class="app-period-switch app-period-switch--inline">
        <span class="app-muted">Période</span>
        <a href="{{ $periodBase }}?period=week" class="app-text-link @if($activePeriod==='week') is-active @endif">Semaine</a>
        <span class="app-muted" aria-hidden="true">·</span>
        <a href="{{ $periodBase }}?period=month" class="app-text-link @if($activePeriod==='month') is-active @endif">Mois</a>
        <span class="app-muted" aria-hidden="true">·</span>
        <a href="{{ $periodBase }}?period=year" class="app-text-link @if($activePeriod==='year') is-active @endif">Année</a>
    </p>
</div>
@endif
    <div class="app-kpi-grid app-kpi-grid--home app-home-carousel" id="home-kpi-carousel" role="list">
        @foreach ($dash['kpis'] as $key => $kpi)
            @php
                $kLabel = (string) ($dash['kpi_labels'][$key] ?? '');
                $isMoneyKpi = str_ends_with((string) $key, '_fcfa')
                    || str_contains(mb_strtolower($kLabel), 'fcfa');
                $kpiIcon = $kpiIcons[$loop->index % count($kpiIcons)];
            @endphp
            <div class="app-kpi-card app-kpi-card--home" role="listitem">
                <span class="app-kpi-card__icon" aria-hidden="true">@include('app.partials.app-nav-icon', ['name' => $kpiIcon])</span>
                <div class="app-kpi-card__label">{{ $kLabel !== '' ? $kLabel : \Illuminate\Support\Str::of($key)->replace('_', ' ')->title() }}</div>
                @if (is_array($kpi) && array_key_exists('value', $kpi))
                    <div class="app-kpi-card__value">
                        {{ number_format((int) $kpi['value'], 0, ',', ' ') }}@if ($isMoneyKpi)<span class="app-muted app-text-sm"> FCFA</span>@endif
                    </div>
                    @if (isset($kpi['trend_pct']))
                        <div class="app-kpi-card__trend {{ ! empty($kpi['trend_up']) ? 'is-up' : 'is-down' }}">
                            {{ ! empty($kpi['trend_up']) ? '↑' : '↓' }} {{ (int) $kpi['trend_pct'] }} %
                        </div>
                    @endif
                @else
                    <div class="app-kpi-card__value">{{ is_numeric($kpi) ? number_format((int) $kpi, 0, ',', ' ') : e($kpi) }}</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@if (! $dashFull)
    </div>
</section>
@endif
@endif

@if ($dashFull && ! empty($dash['charts']))
    <div class="app-card app-mt">
        <h2 class="app-section-title app-section-title--flush">Graphiques</h2>

        @if ($profileSlug === 'particulier')
            <div class="app-charts-grid">
                <div class="app-chart-card">
                    <h3 class="app-chart-card__title">{{ $dash['charts']['panel_titles']['btp'] ?? 'BTP' }}</h3>
                    <div class="app-chart-wrap"><canvas id="app-chart-part-btp" height="220"></canvas></div>
                </div>
                <div class="app-chart-card">
                    <h3 class="app-chart-card__title">{{ $dash['charts']['panel_titles']['artisan'] ?? 'Artisans' }}</h3>
                    <div class="app-chart-wrap"><canvas id="app-chart-part-artisan" height="220"></canvas></div>
                </div>
                <div class="app-chart-card">
                    <h3 class="app-chart-card__title">{{ $dash['charts']['panel_titles']['commandes'] ?? 'Commandes par jour' }}</h3>
                    <div class="app-chart-wrap"><canvas id="app-chart-part-cmd" height="220"></canvas></div>
                </div>
                <div class="app-chart-card">
                    <h3 class="app-chart-card__title">{{ $dash['charts']['panel_titles']['ca'] ?? 'Valeur des commandes' }}</h3>
                    <div class="app-chart-wrap"><canvas id="app-chart-part-ca" height="220"></canvas></div>
                </div>
            </div>
        @elseif ($profileSlug === 'artisan')
            <div class="app-chart-card">
                <h3 class="app-chart-card__title">{{ $dash['chart_main_title'] ?? 'Indicateurs' }}</h3>
                <div class="app-chart-wrap app-chart-wrap--wide"><canvas id="app-chart-artisan-main" height="280"></canvas></div>
            </div>
        @endif
    </div>
@endif

@if ($dashFull && $profileSlug === 'fournisseur' && (! empty($dash['pie']) || ! empty($dash['sales_by_month'])))
    <div class="app-card app-mt">
        <h2 class="app-section-title app-section-title--flush">Graphiques</h2>
        <div class="app-charts-grid">
            @if (! empty($dash['pie']))
                <div class="app-chart-card">
                    <h3 class="app-chart-card__title">Répartition des commandes</h3>
                    <div class="app-chart-wrap"><canvas id="app-chart-fournisseur-pie" height="240"></canvas></div>
                </div>
            @endif
            @if (! empty($dash['sales_by_month']))
                <div class="app-chart-card">
                    <h3 class="app-chart-card__title">Commandes par mois (6 derniers)</h3>
                    <div class="app-chart-wrap"><canvas id="app-chart-fournisseur-sales" height="240"></canvas></div>
                </div>
            @endif
        </div>
    </div>
@endif

@if ($dashFull && $profileSlug === 'batiment' && ! empty($batimentAnalytics))
    <div class="app-card app-mt">
        <h2 class="app-section-title">Services (période)</h2>
        @php $split = $batimentAnalytics['services_split'] ?? []; @endphp
        <ul class="app-inline-stats">
            <li><span class="app-muted">Total créés</span> <strong>{{ (int) ($split['total'] ?? 0) }}</strong></li>
            <li><span class="app-muted">Actifs (approuvés)</span> <strong>{{ (int) ($split['actifs'] ?? 0) }}</strong></li>
            <li><span class="app-muted">Non disponibles</span> <strong>{{ (int) ($split['non_disponibles'] ?? 0) }}</strong></li>
        </ul>
    </div>
    <div class="app-card app-mt">
        <h2 class="app-section-title">Tendances BTP</h2>
        <div class="app-charts-grid app-charts-grid--triple">
            <div class="app-chart-card">
                <h3 class="app-chart-card__title">Devis</h3>
                <div class="app-chart-wrap"><canvas id="app-chart-btp-devis" height="200"></canvas></div>
            </div>
            <div class="app-chart-card">
                <h3 class="app-chart-card__title">Besoins publiés</h3>
                <div class="app-chart-wrap"><canvas id="app-chart-btp-besoins" height="200"></canvas></div>
            </div>
            <div class="app-chart-card">
                <h3 class="app-chart-card__title">Services actifs créés</h3>
                <div class="app-chart-wrap"><canvas id="app-chart-btp-srv" height="200"></canvas></div>
            </div>
        </div>
    </div>
@endif

@if (! empty($dash['pie']) && ! ($dashFull && $profileSlug === 'fournisseur'))
    <div class="app-card app-mt">
        <h2 class="app-section-title">Répartition des commandes</h2>
        <table class="app-table">
            <thead><tr><th>Statut</th><th>Nombre</th><th>%</th></tr></thead>
            <tbody>
                @foreach ($dash['pie'] as $slice)
                    <tr>
                        <td>{{ $slice['label'] ?? $slice['key'] }}</td>
                        <td>{{ $slice['count'] ?? '—' }}</td>
                        <td>{{ $slice['percent'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if (! empty($dash['sales_by_month']) && ! ($dashFull && $profileSlug === 'fournisseur'))
    <div class="app-card app-mt">
        <h2 class="app-section-title">Commandes par mois (6 derniers)</h2>
        <table class="app-table">
            <thead><tr><th>Mois</th><th>Nombre</th></tr></thead>
            <tbody>
                @foreach ($dash['sales_by_month'] as $row)
                    <tr>
                        <td>{{ $row['label'] ?? ($row['month'] ?? '—') }}</td>
                        <td>{{ $row['count'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@php
    $needsChartJs = $dashFull && (
        (! empty($dash['charts']) && in_array($profileSlug, ['particulier', 'artisan'], true))
        || ($profileSlug === 'batiment' && ! empty($batimentAnalytics))
        || ($profileSlug === 'fournisseur' && (! empty($dash['pie']) || ! empty($dash['sales_by_month'])))
    );
@endphp
@if ($needsChartJs)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;
            const slug = @json($profileSlug);
            const charts = @json($dash['charts'] ?? []);
            const batA = @json($batimentAnalytics ?? null);
            const navy = '#001f3f';
            const orange = '#f19b34';
            const teal = '#0d9488';

            if (slug === 'particulier' && charts.week_labels) {
                const labels = charts.week_labels;
                const dl = charts.dataset_labels || {};
                const opts = { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { precision: 0 } } } };

                if (charts.btp && document.getElementById('app-chart-part-btp')) {
                    new Chart(document.getElementById('app-chart-part-btp'), {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                { label: dl.demandes || 'Demandes', data: charts.btp.demandes_totales || [], backgroundColor: navy + 'cc' },
                                { label: dl.prestations_acceptees || 'Prestations acceptées', data: charts.btp.prestations_acceptees || [], backgroundColor: orange + 'cc' },
                            ],
                        },
                        options: opts,
                    });
                }
                if (charts.artisan && document.getElementById('app-chart-part-artisan')) {
                    new Chart(document.getElementById('app-chart-part-artisan'), {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                { label: dl.demandes || 'Demandes', data: charts.artisan.demandes_totales || [], backgroundColor: navy + 'cc' },
                                { label: dl.prestations_acceptees || 'Prestations acceptées', data: charts.artisan.prestations_acceptees || [], backgroundColor: orange + 'cc' },
                            ],
                        },
                        options: opts,
                    });
                }
                if (document.getElementById('app-chart-part-cmd')) {
                    new Chart(document.getElementById('app-chart-part-cmd'), {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{ label: dl.commandes || 'Commandes', data: charts.commandes_par_jour || [], backgroundColor: teal + 'cc' }],
                        },
                        options: opts,
                    });
                }
                if (document.getElementById('app-chart-part-ca')) {
                    new Chart(document.getElementById('app-chart-part-ca'), {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{ label: dl.fcfa || 'FCFA', data: charts.valeur_par_jour_fcfa || [], borderColor: orange, backgroundColor: orange + '33', fill: true, tension: 0.25 }],
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } },
                    });
                }
            }

            if (slug === 'artisan' && charts.x_labels && document.getElementById('app-chart-artisan-main')) {
                const labels = charts.x_labels;
                const dl = charts.dataset_labels || {};
                new Chart(document.getElementById('app-chart-artisan-main'), {
                    data: {
                        labels,
                        datasets: [
                            {
                                type: 'line',
                                label: dl.revenue || 'Revenu (milliers FCFA)',
                                data: charts.revenu_par_jour_k || [],
                                borderColor: orange,
                                backgroundColor: orange + '22',
                                yAxisID: 'y',
                                tension: 0.2,
                                fill: false,
                            },
                            {
                                type: 'bar',
                                label: dl.envoyees || 'Candidatures envoyées',
                                data: charts.candidatures_envoyees_par_jour || [],
                                backgroundColor: navy + 'aa',
                                yAxisID: 'y1',
                            },
                            {
                                type: 'bar',
                                label: dl.acceptees || 'Candidatures acceptées',
                                data: charts.candidatures_accepte_par_jour || [],
                                backgroundColor: teal + 'aa',
                                yAxisID: 'y1',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                title: { display: true, text: 'Revenu (k FCFA)' },
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                grid: { drawOnChartArea: false },
                                title: { display: true, text: 'Nombre' },
                            },
                        },
                    },
                });
            }

            if (slug === 'batiment' && batA && batA.devis_series && document.getElementById('app-chart-btp-devis')) {
                const lineOpts = { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } };
                const ds = (s) => ({ labels: s.labels || [], datasets: [{ label: 'Nombre', data: s.values || [], borderColor: orange, backgroundColor: orange + '33', fill: true, tension: 0.2 }] });
                new Chart(document.getElementById('app-chart-btp-devis'), { type: 'line', data: ds(batA.devis_series), options: lineOpts });
                new Chart(document.getElementById('app-chart-btp-besoins'), { type: 'line', data: ds(batA.besoins_series), options: lineOpts });
                new Chart(document.getElementById('app-chart-btp-srv'), { type: 'line', data: ds(batA.services_actifs_series), options: lineOpts });
            }

            if (slug === 'fournisseur') {
                const pieSlices = @json($dash['pie'] ?? []);
                const salesMo = @json($dash['sales_by_month'] ?? []);
                const pieEl = document.getElementById('app-chart-fournisseur-pie');
                if (pieEl && pieSlices.length) {
                    const col = (k) => (k === 'traite' ? navy : k === 'en_cours' ? orange : '#ef4444');
                    new Chart(pieEl, {
                        type: 'doughnut',
                        data: {
                            labels: pieSlices.map((s) => s.label || s.key),
                            datasets: [{ data: pieSlices.map((s) => s.count || 0), backgroundColor: pieSlices.map((s) => col(s.key) + 'cc') }],
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
                    });
                }
                const barEl = document.getElementById('app-chart-fournisseur-sales');
                if (barEl && salesMo.length) {
                    new Chart(barEl, {
                        type: 'bar',
                        data: {
                            labels: salesMo.map((r) => r.label || ''),
                            datasets: [{ label: 'Commandes', data: salesMo.map((r) => r.count || 0), backgroundColor: teal + 'cc' }],
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
                    });
                }
            }
        });
    </script>
    @endpush
@endif
