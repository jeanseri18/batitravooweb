@extends('vitrine.btp_layout')

@section('title', 'Politique de confidentialité — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Politique de confidentialité BATITRAVOO — données collectées, finalités et droits des utilisateurs.')

@section('content')
    <div class="content-page">
        <div class="content-page__inner">
            <nav class="content-page__breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span>Confidentialité</span>
            </nav>

            <header class="content-page__hero">
                <div class="content-page__hero-main">
                    <p class="content-page__eyebrow">Protection des données</p>
                    <h1 class="content-page__title">Politique de confidentialité</h1>
                    <p class="content-page__lead">
                        Nous protégeons vos données et limitons leur utilisation au strict nécessaire. Pour toute demande, contactez-nous via
                        <a href="{{ route('vitrine.contact') }}">Contact</a>.
                    </p>
                </div>
                <div class="content-page__pills" aria-hidden="true">
                    <span class="content-pill">Données minimales</span>
                    <span class="content-pill">Droits des utilisateurs</span>
                </div>
            </header>

            <div class="content-page__grid content-page__grid--2">
                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l8 4v6c0 5-3.5 9-8 10C7.5 21 4 17 4 12V6l8-4Z" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Données collectées
                    </h2>
                    <ul>
                        <li>Données de compte : nom, e-mail, téléphone (selon profil)</li>
                        <li>Données de profil : localisation, informations métier</li>
                        <li>Données d’usage : messages, interactions, contenus publiés</li>
                    </ul>
                </section>

                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 19V5a2 2 0 012-2h12a2 2 0 012 2v14" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M8 7h8M8 11h8M8 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        Finalités
                    </h2>
                    <ul>
                        <li>Créer et gérer votre compte</li>
                        <li>Fournir les fonctionnalités de mise en relation et marketplace</li>
                        <li>Assurer la sécurité et prévenir la fraude</li>
                        <li>Support et amélioration du service</li>
                    </ul>
                </section>
            </div>

            <div class="content-page__grid content-page__grid--2">
                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10Z" stroke="currentColor" stroke-width="2" opacity="0.35"/></svg>
                        </span>
                        Conservation
                    </h2>
                    <p>Les données sont conservées le temps nécessaire à la fourniture du service et au respect des obligations légales.</p>
                </section>

                <section class="content-card">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 11a3 3 0 100-6 3 3 0 000 6Z" stroke="currentColor" stroke-width="2"/><path d="M4 21a8 8 0 0116 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        Vos droits
                    </h2>
                    <p>Vous pouvez demander l’accès, la rectification ou la suppression de vos données via <a href="{{ route('vitrine.contact') }}">Contact</a>.</p>
                    <p class="content-muted"><strong>Dernière mise à jour :</strong> {{ now()->format('d/m/Y') }}</p>
                </section>
            </div>

            <section class="content-card">
                <h2 class="content-card__title">Cookies & traceurs</h2>
                <p class="content-muted">Des cookies techniques peuvent être utilisés pour le fonctionnement du site et de l’application. Vous pouvez configurer votre navigateur pour les limiter.</p>
            </section>

            <section class="content-card">
                <h2 class="content-card__title">Documents associés</h2>
                <p>Consultez nos <a href="{{ route('vitrine.terms') }}">conditions générales d’utilisation</a>.</p>
            </section>
        </div>
    </div>
@endsection
