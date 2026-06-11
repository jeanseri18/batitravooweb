@extends('vitrine.btp_layout')

@section('title', 'Annuaire — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Annuaire BATITRAVOO — Fournisseurs, artisans et entreprises BTP qualifiés en Côte d\'Ivoire.')

@section('content')
    <div class="annuaire-page">
        <div class="annuaire-page__inner">
            <header class="annuaire-hero">
                <p class="annuaire-hero__eyebrow">Répertoire professionnel</p>
                <h1 class="annuaire-hero__title">Annuaire BTP</h1>
                <p class="annuaire-hero__lead">
                    Retrouvez les fournisseurs, artisans et entreprises du bâtiment référencés sur BATITRAVOO.
                </p>
            </header>

            <div class="annuaire-toolbar">
                <nav class="annuaire-tabs" aria-label="Filtrer par type de profil">
                    <a href="{{ route('vitrine.annuaire', array_filter(['q' => $q ?: null])) }}"
                       @class(['annuaire-tabs__item', 'is-active' => $kind === ''])>
                        Tous
                    </a>
                    <a href="{{ route('vitrine.annuaire', array_filter(['kind' => 'fournisseur', 'q' => $q ?: null])) }}"
                       @class(['annuaire-tabs__item', 'is-active' => $kind === 'fournisseur'])>
                        Fournisseurs
                    </a>
                    <a href="{{ route('vitrine.annuaire', array_filter(['kind' => 'artisan', 'q' => $q ?: null])) }}"
                       @class(['annuaire-tabs__item', 'is-active' => $kind === 'artisan'])>
                        Artisans
                    </a>
                    <a href="{{ route('vitrine.annuaire', array_filter(['kind' => 'btp', 'q' => $q ?: null])) }}"
                       @class(['annuaire-tabs__item', 'is-active' => $kind === 'btp'])>
                        Entreprises BTP
                    </a>
                </nav>

                <form class="annuaire-search" method="get" action="{{ route('vitrine.annuaire') }}" role="search">
                    @if ($kind !== '')
                        <input type="hidden" name="kind" value="{{ $kind }}">
                    @endif
                    <label class="visually-hidden" for="annuaire-q">Rechercher</label>
                    <input
                        id="annuaire-q"
                        type="search"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Nom, entreprise, ville, activité…"
                        autocomplete="off"
                    >
                    <button type="submit" class="annuaire-search__btn">Rechercher</button>
                </form>
            </div>

            <p class="annuaire-count">
                {{ $total }} {{ $total > 1 ? 'profils référencés' : 'profil référencé' }}
            </p>

            @if (count($providers) === 0)
                <div class="annuaire-empty">
                    <p>Aucun profil ne correspond à votre recherche pour le moment.</p>
                    <p class="annuaire-empty__hint">Élargissez les filtres ou <a href="{{ route('register') }}">inscrivez votre entreprise</a>.</p>
                </div>
            @else
                <ul class="annuaire-grid">
                    @foreach ($providers as $provider)
                        @php
                            $profileLabel = match ($provider['profile_type'] ?? '') {
                                'entreprise_fournisseur' => 'Fournisseur',
                                'artisan' => 'Artisan',
                                'entrepreneur_batiment' => 'Entreprise BTP',
                                default => 'Prestataire',
                            };
                            $badgeClass = match ($provider['profile_type'] ?? '') {
                                'entreprise_fournisseur' => 'annuaire-badge--fournisseur',
                                'artisan' => 'annuaire-badge--artisan',
                                'entrepreneur_batiment' => 'annuaire-badge--btp',
                                default => '',
                            };
                        @endphp
                        <li class="annuaire-card">
                            <div class="annuaire-card__head">
                                @if (! empty($provider['avatar_url']))
                                    <img
                                        class="annuaire-card__avatar"
                                        src="{{ $provider['avatar_url'] }}"
                                        alt=""
                                        width="56"
                                        height="56"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="annuaire-card__avatar annuaire-card__avatar--placeholder" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($provider['display_name'] ?? 'P', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="annuaire-card__meta">
                                    <h2 class="annuaire-card__name">{{ $provider['display_name'] }}</h2>
                                    <span class="annuaire-badge {{ $badgeClass }}">{{ $profileLabel }}</span>
                                </div>
                            </div>
                            @if (! empty($provider['activity']))
                                <p class="annuaire-card__activity">{{ $provider['activity'] }}</p>
                            @endif
                            @if (! empty($provider['location']))
                                <p class="annuaire-card__location">{{ $provider['location'] }}</p>
                            @endif
                            <div class="annuaire-card__actions">
                                <a href="{{ route('register') }}" class="annuaire-card__cta">Contacter via BATITRAVOO</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
