@include('app.shell.partials.home_hero', [
    'heroCtaLabel' => '+ Publier un besoin',
    'heroCtaUrl' => route('app.batiment.besoins.create'),
])

@if (! empty($dashboard['kpis']))
    @include('app.shell.partials.dashboard_metrics')
@endif

@include('app.shell.partials.home_search', [
    'searchAction' => route('app.batiment.marketplace'),
    'searchLabel' => 'Recherche marketplace',
    'searchPlaceholder' => 'Rechercher annonces, prestations…',
    'searchId' => 'batiment-home-search',
])

@include('app.shell.partials.home_shortcuts')
