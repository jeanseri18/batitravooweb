@extends('vitrine.btp_layout')

@section('title', 'Contact — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Contactez BATITRAVOO — support, partenariat et démonstration.')

@section('content')
    <div class="content-page">
        <div class="content-page__inner">
            <nav class="content-page__breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span>Contact</span>
            </nav>

            <header class="content-page__hero">
                <div class="content-page__hero-main">
                    <p class="content-page__eyebrow">Support & partenariat</p>
                    <h1 class="content-page__title">Contact</h1>
                    <p class="content-page__lead">
                        Une question, un retour ou un besoin de démonstration ? Écrivez-nous, nous vous répondons rapidement.
                    </p>
                </div>
                <div class="content-page__pills" aria-hidden="true">
                    <span class="content-pill">Réponse rapide (heures ouvrées)</span>
                    <span class="content-pill">Support & assistance</span>
                </div>
            </header>

            <div class="content-page__grid content-page__grid--2">
                <section class="content-card" aria-label="Coordonnées">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 4h16v16H4V4Z" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M4 7l8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Coordonnées
                    </h2>
                    <p>
                        <strong>E-mail</strong><br>
                        <a href="mailto:support@batitravoo.com">support@batitravoo.com</a>
                    </p>
                    <p>
                        <strong>WhatsApp</strong><br>
                        <a href="https://wa.me/" rel="noopener noreferrer">Démarrer une conversation</a>
                    </p>
                    <p class="content-muted">
                        <strong>Horaires</strong> : Lundi – Samedi, 08:00 – 18:00.
                    </p>
                </section>

                <section class="content-card" aria-label="Formulaire">
                    <h2 class="content-card__title">
                        <span class="content-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h10M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        Écrire un message
                    </h2>

                    <form action="mailto:support@batitravoo.com" method="post" enctype="text/plain" class="content-form">
                        <div class="auth-field">
                            <label for="name">Nom</label>
                            <input id="name" name="Nom" type="text" placeholder="Votre nom" autocomplete="name">
                        </div>
                        <div class="auth-field">
                            <label for="email">E-mail</label>
                            <input id="email" name="Email" type="email" placeholder="vous@exemple.com" autocomplete="email">
                        </div>
                        <div class="auth-field">
                            <label for="subject">Objet</label>
                            <input id="subject" name="Objet" type="text" placeholder="Support, partenariat, démonstration…">
                        </div>
                        <div class="auth-field">
                            <label for="body">Message</label>
                            <textarea id="body" name="Message" placeholder="Décrivez votre demande…"></textarea>
                        </div>
                        <button type="submit" class="auth-submit">Envoyer</button>
                        <p class="content-form__hint">
                            Ce formulaire ouvre votre client e-mail (aucune donnée n’est stockée sur le site vitrine).
                        </p>
                    </form>
                </section>
            </div>

            <section class="content-card" aria-label="Ressources">
                <h2 class="content-card__title">
                    <span class="content-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 18h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M9.5 9a2.5 2.5 0 115 0c0 2-2.5 1.75-2.5 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10Z" stroke="currentColor" stroke-width="2" opacity="0.35"/></svg>
                    </span>
                    Avant de nous écrire
                </h2>
                <p class="content-muted">Les réponses les plus courantes sont souvent déjà disponibles ici :</p>
                <ul>
                    <li><a href="{{ route('vitrine.help_center') }}">Centre d’aide</a></li>
                    <li><a href="{{ route('vitrine.faq') }}">FAQ</a></li>
                    <li><a href="{{ route('vitrine.terms') }}">Conditions d’utilisation</a> et <a href="{{ route('vitrine.privacy') }}">politique de confidentialité</a></li>
                </ul>
            </section>
        </div>
    </div>
@endsection
