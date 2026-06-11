@include('app.shell.partials.home_hero', [
    'heroCtaLabel' => '+ Ajouter un service',
    'heroCtaUrl' => route('app.artisan.services.create'),
])

@if (! empty($dashboard['kpis']))
    @include('app.shell.partials.dashboard_metrics')
@endif

@include('app.shell.partials.home_search', [
    'searchAction' => route('app.artisan.marketplace'),
    'searchLabel' => 'Recherche marketplace',
    'searchPlaceholder' => 'Besoins, prestations, matériaux…',
    'searchId' => 'artisan-home-search',
    'searchHiddenFields' => ['tab' => 'besoins'],
])

@include('app.shell.partials.home_shortcuts')
