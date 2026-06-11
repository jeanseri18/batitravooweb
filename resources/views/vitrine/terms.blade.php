@extends('vitrine.btp_layout')

@section('title', "Conditions d'utilisation — ".config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Conditions générales d’utilisation de la plateforme BATITRAVOO.')

@section('content')
    <div class="content-page">
        <div class="content-page__inner">
            <nav class="content-page__breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span>Conditions d’utilisation</span>
            </nav>

            <header class="content-page__hero">
                <div class="content-page__hero-main">
                    <p class="content-page__eyebrow">Document légal</p>
                    <h1 class="content-page__title">Conditions d'utilisation</h1>
                    <p class="content-page__lead">
                        En utilisant BATITRAVOO, vous acceptez les conditions ci-dessous. Pour une question, consultez le
                        <a href="{{ route('vitrine.help_center') }}">centre d’aide</a>.
                    </p>
                </div>
                <div class="content-page__pills" aria-hidden="true">
                    <span class="content-pill">Simple & lisible</span>
                    <span class="content-pill">Modération possible</span>
                </div>
            </header>

            <div class="content-page__grid content-page__grid--2">
                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M7 3h10v4H7V3Z" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M7 7h10v14H7V7Z" stroke="currentColor" stroke-width="2"/><path d="M9 11h6M9 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        1. Objet
                    </h2>
                    <p>BATITRAVOO met en relation les acteurs du bâtiment et propose des fonctionnalités de publication, messagerie et marketplace.</p>
                </section>

                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 11a3 3 0 100-6 3 3 0 000 6Z" stroke="currentColor" stroke-width="2"/><path d="M4 21a8 8 0 0116 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        2. Comptes
                    </h2>
                    <p>Vous êtes responsable des informations fournies lors de l’inscription et de la confidentialité de vos accès.</p>
                </section>
            </div>

            <section class="content-card">
                <h2 class="content-card__title">
                    <span class="content-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l9 4-9 4-9-4 9-4Z" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M3 10v8l9 4 9-4v-8" stroke="currentColor" stroke-width="2"/></svg>
                    </span>
                    3. Contenus & conformité
                </h2>
                <p class="content-muted">Vous vous engagez à publier des contenus exacts, licites et respectueux. Les contenus peuvent être modérés.</p>
                <ul>
                    <li>Pas d’usurpation d’identité ni de contenu trompeur</li>
                    <li>Pas de contenu illégal, haineux, violent ou discriminatoire</li>
                    <li>Respect des droits (images, marques, textes)</li>
                </ul>
            </section>

            <div class="content-page__grid content-page__grid--2">
                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M21 15a4 4 0 01-4 4H8l-5 3V7a4 4 0 014-4h10a4 4 0 014 4v8Z" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M8 8h8M8 12h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        4. Support
                    </h2>
                    <p>Pour toute demande, utilisez la page <a href="{{ route('vitrine.contact') }}">Contact</a> ou le <a href="{{ route('vitrine.help_center') }}">centre d’aide</a>.</p>
                </section>

                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10Z" stroke="currentColor" stroke-width="2" opacity="0.35"/></svg>
                        </span>
                        5. Mise à jour
                    </h2>
                    <p>Ces conditions peuvent évoluer. La date de mise à jour est affichée sur cette page.</p>
                    <p class="content-muted"><strong>Dernière mise à jour :</strong> {{ now()->format('d/m/Y') }}</p>
                </section>
            </div>

            <section class="content-card">
                <h2 class="content-card__title">6. Responsabilité</h2>
                <p class="content-muted">BATITRAVOO met en relation des professionnels et des particuliers. Les prestations sont conclues directement entre les parties. La plateforme n’est pas partie aux contrats de travaux ou de fourniture, sauf mention contraire.</p>
            </section>

            <section class="content-card">
                <h2 class="content-card__title">7. Documents légaux associés</h2>
                <p>Consultez également notre <a href="{{ route('vitrine.privacy') }}">politique de confidentialité</a>.</p>
            </section>
        </div>
    </div>
@endsection
