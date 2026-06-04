@extends('vitrine.layout')

@section('title', 'Politique de confidentialité — '.config('app.name', 'BATITRAVOO'))
@section('h1', 'Politique de confidentialité')
@section('subtitle')
    Nous protégeons vos données et nous limitons leur utilisation au strict nécessaire.
@endsection

@section('content')
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Accueil</a> <span class="crumb-dot" aria-hidden="true"></span> <span>Confidentialité</span>
    </div>

    <div class="page-hero">
        <div>
            <h2 style="margin-top:0">Transparence & contrôle</h2>
            <p class="muted">
                Cette page explique quelles données sont utilisées et pourquoi. Pour toute demande, contactez-nous via
                <a href="{{ route('vitrine.contact') }}">Contact</a>.
            </p>
        </div>
        <aside class="hero-aside">
            <div class="pill"><span class="dot" aria-hidden="true"></span> Données minimales</div>
            <div class="pill"><span class="dot" aria-hidden="true"></span> Droits des utilisateurs</div>
        </aside>
    </div>

    <div class="grid-2">
        <section class="card">
            <div class="card-title">
                <span class="icon-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2l8 4v6c0 5-3.5 9-8 10C7.5 21 4 17 4 12V6l8-4Z" stroke="currentColor" stroke-width="2" opacity="0.35"/>
                        <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Données collectées</span>
            </div>
            <ul>
                <li>Données de compte : nom, e-mail, téléphone (selon profil)</li>
                <li>Données de profil : localisation, informations métier</li>
                <li>Données d’usage : messages, interactions, contenus publiés</li>
            </ul>
        </section>

        <section class="card">
            <div class="card-title">
                <span class="icon-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 19V5a2 2 0 012-2h12a2 2 0 012 2v14" stroke="currentColor" stroke-width="2" opacity="0.35"/>
                        <path d="M8 7h8M8 11h8M8 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Finalités</span>
            </div>
            <ul>
                <li>Créer et gérer votre compte</li>
                <li>Fournir les fonctionnalités de mise en relation et marketplace</li>
                <li>Assurer la sécurité et prévenir la fraude</li>
                <li>Support et amélioration du service</li>
            </ul>
        </section>
    </div>

    <div class="grid-2" style="margin-top:1.25rem">
        <section class="card">
            <div class="card-title">
                <span class="icon-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10Z" stroke="currentColor" stroke-width="2" opacity="0.35"/>
                    </svg>
                </span>
                <span>Conservation</span>
            </div>
            <p>Les données sont conservées le temps nécessaire à la fourniture du service et au respect des obligations légales.</p>
        </section>

        <section class="card">
            <div class="card-title">
                <span class="icon-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 11a3 3 0 100-6 3 3 0 000 6Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 21a8 8 0 0116 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Vos droits</span>
            </div>
            <p>Vous pouvez demander l’accès, la rectification ou la suppression de vos données via <a href="{{ route('vitrine.contact') }}">Contact</a>.</p>
            <p class="muted" style="margin-top:0.75rem"><strong>Dernière mise à jour :</strong> {{ now()->format('d/m/Y') }}</p>
        </section>
    </div>

    <section class="card" style="margin-top:1.25rem">
        <div class="card-title"><span>Cookies & traceurs</span></div>
        <p class="muted">Des cookies techniques peuvent être utilisés pour le fonctionnement du site et de l’application. Vous pouvez configurer votre navigateur pour les limiter.</p>
    </section>

    <section class="card" style="margin-top:1.25rem">
        <div class="card-title"><span>Documents associés</span></div>
        <p>Consultez nos <a href="{{ route('vitrine.terms') }}">conditions générales d’utilisation</a>.</p>
    </section>
@endsection
