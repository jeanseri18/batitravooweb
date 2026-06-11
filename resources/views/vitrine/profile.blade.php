@extends('vitrine.btp_layout')

@section('title', $profile['title'].' — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', $profile['lead'])

@section('content')
    <div class="profile-landing">
        <div class="profile-landing__inner">
            <nav class="profile-landing__breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <a href="{{ url('/') }}#pour-qui">Métiers</a>
                <span aria-hidden="true">/</span>
                <span>{{ $profile['title'] }}</span>
            </nav>

            <header class="profile-landing__hero">
                <p class="profile-landing__eyebrow">{{ $profile['num'] }}</p>
                <div class="profile-landing__hero-row">
                    <div>
                        <h1 class="profile-landing__title">{{ $profile['title'] }}</h1>
                        <p class="profile-landing__lead">{{ $profile['lead'] }}</p>
                    </div>
                    <div class="profile-landing__icon" aria-hidden="true">
                        <img src="{{ asset($profile['icon']) }}" alt="" width="72" height="72" loading="lazy">
                    </div>
                </div>
            </header>

            <section class="profile-landing__benefits" aria-labelledby="benefits-title">
                <h2 id="benefits-title" class="profile-landing__section-title">Ce que vous pouvez faire</h2>
                <ul class="profile-landing__list">
                    @foreach ($profile['benefits'] as $benefit)
                        <li>{{ $benefit }}</li>
                    @endforeach
                </ul>
            </section>

            <section class="profile-landing__cta" aria-labelledby="profile-cta-title">
                <h2 id="profile-cta-title" class="profile-landing__section-title">Rejoindre BATITRAVOO</h2>
                <p class="profile-landing__cta-text">
                    Créez votre compte {{ strtolower($profile['title']) }} et accédez aux outils métier du bâtiment.
                </p>
                <div class="profile-landing__actions">
                    <a href="{{ route('register', ['profil' => $profile['register_profil']]) }}" class="btn btn--primary">Créer mon compte</a>
                    <a href="{{ route('login') }}" class="btn btn--outline">Se connecter</a>
                </div>
            </section>

            <nav class="profile-landing__siblings" aria-label="Autres profils">
                <p class="profile-landing__siblings-label">Découvrir les autres profils</p>
                <ul class="profile-landing__siblings-list">
                    @if ($profile['slug'] !== 'entreprise-btp')
                        <li><a href="{{ route('vitrine.entreprise_btp') }}">Entreprise BTP</a></li>
                    @endif
                    @if ($profile['slug'] !== 'artisan')
                        <li><a href="{{ route('vitrine.artisan') }}">Artisan</a></li>
                    @endif
                    @if ($profile['slug'] !== 'particulier')
                        <li><a href="{{ route('vitrine.particulier') }}">Particulier</a></li>
                    @endif
                    @if ($profile['slug'] !== 'fournisseur')
                        <li><a href="{{ route('vitrine.fournisseur') }}">Fournisseur</a></li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
@endsection
