@php
    $searchAction = $searchAction ?? '#';
    $searchLabel = $searchLabel ?? 'Rechercher';
    $searchPlaceholder = $searchPlaceholder ?? 'Rechercher…';
    $searchId = $searchId ?? 'home-search';
    $searchMethod = $searchMethod ?? 'get';
    $searchLocal = ! empty($searchLocal);
    $searchHiddenFields = is_array($searchHiddenFields ?? null) ? $searchHiddenFields : [];
@endphp

<section class="app-home-block app-home-block--search" aria-labelledby="{{ $searchId }}-label">
    @include('app.shell.partials.home_section_head', [
        'title' => $searchLabel,
        'sectionId' => $searchId.'-label',
    ])
    <div class="app-card app-home-search app-home-section">
    @if ($searchLocal)
        <div class="app-home-search__field">
            <span class="app-home-search__icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
            </span>
            <input type="search" id="{{ $searchId }}" class="app-home-search__input" placeholder="{{ $searchPlaceholder }}" autocomplete="off">
        </div>
    @else
        <form method="{{ $searchMethod }}" action="{{ $searchAction }}" class="app-home-search__form">
            @foreach ($searchHiddenFields as $hiddenName => $hiddenValue)
                <input type="hidden" name="{{ $hiddenName }}" value="{{ $hiddenValue }}">
            @endforeach
            <div class="app-home-search__field">
                <span class="app-home-search__icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </span>
                <input type="search" name="q" id="{{ $searchId }}" class="app-home-search__input" placeholder="{{ $searchPlaceholder }}" value="{{ request('q') }}" autocomplete="off">
                <button type="submit" class="app-home-search__btn">Rechercher</button>
            </div>
        </form>
    @endif
    </div>
</section>
