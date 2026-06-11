@include('app.shell.partials.home_hero', [
    'heroCtaLabel' => 'Demander un devis',
    'heroCtaUrl' => route('app.particulier.devis.create'),
])

@if (! empty($dashboard['kpis']))
    @include('app.shell.partials.dashboard_metrics')
@endif

@include('app.shell.partials.home_search', [
    'searchAction' => route('app.particulier.marketplace'),
    'searchLabel' => 'Rechercher sur le marketplace',
    'searchPlaceholder' => 'Mots-clés, ville, matériaux…',
    'searchId' => 'particulier-home-search',
    'searchHiddenFields' => ['tab' => 'services'],
])

@include('app.shell.partials.home_shortcuts')
