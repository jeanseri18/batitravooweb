@extends('vitrine.btp_layout')

@section('title', 'Centre d’aide — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Centre d’aide BATITRAVOO — guides pour démarrer et résoudre les problèmes courants.')

@section('content')
    <div class="content-page">
        <div class="content-page__inner">
            <nav class="content-page__breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span>Centre d’aide</span>
            </nav>

            <header class="content-page__hero">
                <div class="content-page__hero-main">
                    <p class="content-page__eyebrow">Guides & support</p>
                    <h1 class="content-page__title">Centre d’aide</h1>
                    <p class="content-page__lead">
                        Retrouvez les réponses essentielles pour bien démarrer et résoudre rapidement les problèmes courants.
                    </p>
                </div>
                <div class="content-page__pills" aria-hidden="true">
                    <span class="content-pill">Démarrage en 3 minutes</span>
                    <span class="content-pill">Réponses par thème</span>
                </div>
            </header>

            <div class="content-page__grid content-page__grid--2">
                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10Z" stroke="currentColor" stroke-width="2" opacity="0.35"/></svg>
                        </span>
                        Premiers pas
                    </h2>
                    <ul>
                        <li>Créer un compte et compléter son profil</li>
                        <li>Publier un besoin (Particulier / BTP)</li>
                        <li>Créer un service (Artisan / BTP)</li>
                        <li>Commander des matériaux (Marketplace)</li>
                    </ul>
                </section>

                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 11a3 3 0 100-6 3 3 0 000 6Z" stroke="currentColor" stroke-width="2"/><path d="M4 21a8 8 0 0116 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        Compte & sécurité
                    </h2>
                    <ul>
                        <li>Mot de passe oublié / changement de mot de passe</li>
                        <li>Gestion du profil et de la localisation</li>
                        <li>Bonnes pratiques : garder un profil à jour</li>
                    </ul>
                </section>
            </div>

            <section class="content-card">
                <h2 class="content-card__title">
                    <span class="content-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M21 15a4 4 0 01-4 4H8l-5 3V7a4 4 0 014-4h10a4 4 0 014 4v8Z" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M8 8h8M8 12h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    Besoin d’assistance ?
                </h2>
                <p class="content-muted">
                    Consultez la <a href="{{ route('vitrine.faq') }}">FAQ</a> ou contactez-nous via la page
                    <a href="{{ route('vitrine.contact') }}">Contact</a>.
                </p>
            </section>
        </div>
    </div>
@endsection
