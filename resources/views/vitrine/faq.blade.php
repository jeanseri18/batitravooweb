@extends('vitrine.btp_layout')

@section('title', 'FAQ — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Questions fréquentes sur BATITRAVOO — comptes, marketplace, devis et support.')

@section('content')
    <div class="content-page">
        <div class="content-page__inner">
            <nav class="content-page__breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span>FAQ</span>
            </nav>

            <header class="content-page__hero">
                <div class="content-page__hero-main">
                    <p class="content-page__eyebrow">Aide rapide</p>
                    <h1 class="content-page__title">FAQ</h1>
                    <p class="content-page__lead">
                        Ouvrez une question pour voir la réponse. Si vous ne trouvez pas, écrivez-nous via
                        <a href="{{ route('vitrine.contact') }}">Contact</a>.
                    </p>
                </div>
                <div class="content-page__pills" aria-hidden="true">
                    <span class="content-pill">Comptes & accès</span>
                    <span class="content-pill">Marketplace & devis</span>
                </div>
            </header>

            <div class="content-accordion" role="region" aria-label="Questions fréquentes">
                <details class="content-accordion__item">
                    <summary>
                        Qui peut utiliser BATITRAVOO ?
                        <svg class="content-accordion__chev" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </summary>
                    <div class="content-accordion__body">
                        <p>Les particuliers, artisans, entreprises BTP et fournisseurs.</p>
                    </div>
                </details>

                <details class="content-accordion__item">
                    <summary>
                        Comment publier un besoin ?
                        <svg class="content-accordion__chev" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </summary>
                    <div class="content-accordion__body">
                        <p>Après inscription et connexion, allez dans l’onglet « Besoins », puis « Nouveau besoin ».</p>
                    </div>
                </details>

                <details class="content-accordion__item">
                    <summary>
                        Comment commander des matériaux ?
                        <svg class="content-accordion__chev" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </summary>
                    <div class="content-accordion__body">
                        <p>Accédez à la marketplace, ajoutez les produits au panier, puis validez la commande.</p>
                    </div>
                </details>

                <details class="content-accordion__item">
                    <summary>
                        Je n’arrive pas à me connecter, que faire ?
                        <svg class="content-accordion__chev" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </summary>
                    <div class="content-accordion__body">
                        <p>Vérifiez votre e-mail et votre mot de passe. Si besoin, contactez-nous via <a href="{{ route('vitrine.contact') }}">Contact</a>.</p>
                    </div>
                </details>
            </div>
        </div>
    </div>
@endsection
