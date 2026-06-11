@extends('vitrine.btp_layout')

@section('title', config('app.name', 'BATITRAVOO').' — Plateforme professionnelle du bâtiment')
@section('meta_description', 'BATITRAVOO — Plateforme BTP pour entreprises, artisans, fournisseurs et particuliers. Chantiers, devis, recrutement et matériaux.')

@section('content')
    <section class="hero" id="accueil">
        <div class="hero__inner">
            <div class="hero__content">
                <p class="hero__eyebrow">Plateforme BTP · Côte d'Ivoire</p>
                <h1 class="hero__title">
                    Le bâtiment,<br>
                    <em>centralisé et sécurisé.</em>
                </h1>
                <p class="hero__lead">
                    BATITRAVOO connecte entreprises du BTP, artisans, fournisseurs de matériaux et particuliers sur un même espace professionnel : recrutement, devis, marketplace et suivi de chantier.
                </p>
                <div class="hero__actions">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn--primary">Créer un compte</a>
                    @else
                        <a href="#inscription" class="btn btn--primary">Créer un compte</a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn--outline">Se connecter</a>
                    @else
                        <a href="#connexion" class="btn btn--outline">Se connecter</a>
                    @endif
                </div>
            </div>
            <figure class="hero__visual">
                <img src="{{ asset('images/Rectangle 8.png') }}" alt="Équipe professionnelle sur un chantier de construction" width="600" height="450">
                <figcaption class="hero__visual-cap">Chantiers · Travaux · Matériaux</figcaption>
            </figure>
        </div>
        <div class="trust-band" aria-label="Piliers de la plateforme">
            <div class="trust-band__grid">
                <div class="trust-band__item"><strong>01</strong> Recrutement &amp; candidatures</div>
                <div class="trust-band__item"><strong>02</strong> Devis &amp; commandes</div>
                <div class="trust-band__item"><strong>03</strong> Marketplace matériaux</div>
                <div class="trust-band__item"><strong>04</strong> Suivi &amp; support</div>
            </div>
        </div>
    </section>

    <section class="section section-reveal" id="solution" aria-labelledby="solution-title">
        <div class="container">
            <div class="split">
                <div class="split__body">
                    <div class="section__head">
                        <p class="section__label">Notre approche</p>
                        <h2 class="section__title" id="solution-title">Un écosystème BTP structuré</h2>
                        <p class="section__desc">
                            Fini les allers-retours entre artisans, entreprises et fournisseurs. Une seule plateforme pour publier vos besoins, recevoir des candidatures qualifiées et sécuriser vos achats.
                        </p>
                    </div>
                    <ul class="split__list">
                        <li>Profils vérifiés et documents entreprise</li>
                        <li>Devis traçables et historique des échanges</li>
                        <li>Catalogue fournisseurs intégré au workflow chantier</li>
                    </ul>
                </div>
                <figure class="split__media">
                    <img src="{{ asset('images/Photo 2 1.png') }}" alt="Artisan qualifié en intervention" width="600" height="400" loading="lazy">
                </figure>
            </div>
        </div>
    </section>

    <section class="section section--alt section-reveal" id="pour-qui" aria-labelledby="actors-title">
        <div class="container">
            <div class="section__head section__head--center">
                <p class="section__label">Métiers du bâtiment</p>
                <h2 class="section__title" id="actors-title">Une plateforme pour chaque acteur</h2>
            </div>
            <div class="actors-grid">
                <article class="actor-card">
                    <span class="actor-card__num">01 — BTP</span>
                    <div class="actor-card__icon">
                        <img src="{{ asset('images/image 2.png') }}" alt="" width="48" height="48" loading="lazy">
                    </div>
                    <h3 class="actor-card__title">Entreprises BTP</h3>
                    <p class="actor-card__text">Publiez vos besoins, recrutez sur chantier et pilotez candidatures et devis.</p>
                    <p class="actor-card__text"><a href="{{ route('vitrine.entreprise_btp') }}" class="annuaire-card__cta">En savoir plus →</a></p>
                </article>
                <article class="actor-card">
                    <span class="actor-card__num">02 — Artisan</span>
                    <div class="actor-card__icon">
                        <img src="{{ asset('images/image 3.png') }}" alt="" width="48" height="48" loading="lazy">
                    </div>
                    <h3 class="actor-card__title">Artisans</h3>
                    <p class="actor-card__text">Accédez aux opportunités, proposez vos devis et gérez votre vitrine professionnelle.</p>
                    <p class="actor-card__text"><a href="{{ route('vitrine.artisan') }}" class="annuaire-card__cta">En savoir plus →</a></p>
                </article>
                <article class="actor-card">
                    <span class="actor-card__num">03 — Particulier</span>
                    <div class="actor-card__icon">
                        <img src="{{ asset('images/image 4.png') }}" alt="" width="48" height="48" loading="lazy">
                    </div>
                    <h3 class="actor-card__title">Particuliers</h3>
                    <p class="actor-card__text">Trouvez des prestataires fiables, comparez les devis et suivez vos travaux.</p>
                    <p class="actor-card__text"><a href="{{ route('vitrine.particulier') }}" class="annuaire-card__cta">En savoir plus →</a></p>
                </article>
                <article class="actor-card">
                    <span class="actor-card__num">04 — Fournisseur</span>
                    <div class="actor-card__icon">
                        <img src="{{ asset('images/image 5.png') }}" alt="" width="48" height="48" loading="lazy">
                    </div>
                    <h3 class="actor-card__title">Fournisseurs</h3>
                    <p class="actor-card__text">Diffusez votre catalogue, traitez commandes et devis auprès d'une clientèle BTP.</p>
                    <p class="actor-card__text"><a href="{{ route('vitrine.fournisseur') }}" class="annuaire-card__cta">En savoir plus →</a></p>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-reveal" id="fonctionnalites" aria-labelledby="features-title">
        <div class="container">
            <div class="section__head">
                <p class="section__label">Services</p>
                <h2 class="section__title" id="features-title">Outils métier intégrés</h2>
                <p class="section__desc">Quatre modules pour couvrir l'ensemble du cycle projet dans le bâtiment.</p>
            </div>
            <div class="features-grid">
                <article class="feature-card">
                    <div class="feature-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="10.5" cy="10.5" r="5.5" stroke="currentColor" stroke-width="2"/><path d="M16 16l5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h3 class="feature-card__title">Trouver</h3>
                        <p class="feature-card__text">Annuaire d'artisans, entreprises BTP et fournisseurs qualifiés, filtrable par métier et zone.</p>
                        <p class="feature-card__text"><a href="{{ route('vitrine.annuaire') }}" class="annuaire-card__cta">Consulter l'annuaire →</a></p>
                    </div>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M22 2L15 22l-4-9-9-4 18-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <h3 class="feature-card__title">Publier</h3>
                        <p class="feature-card__text">Diffusez vos besoins de chantier et recevez des candidatures adaptées à votre cahier des charges.</p>
                    </div>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M8 13h8M8 17h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h3 class="feature-card__title">Devis &amp; négocier</h3>
                        <p class="feature-card__text">Émettez, comparez et validez des devis détaillés avec traçabilité complète des échanges.</p>
                    </div>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="20" r="1.5" fill="currentColor"/><circle cx="18" cy="20" r="1.5" fill="currentColor"/><path d="M1 1h4l2.68 12.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <h3 class="feature-card__title">Commander</h3>
                        <p class="feature-card__text">Accédez au catalogue matériaux, constituez votre panier et passez commande depuis la plateforme.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="section section--alt section-reveal" id="temoignages" aria-labelledby="testimonials-title">
        <div class="container">
            <div class="section__head section__head--center">
                <p class="section__label">Références clients</p>
                <h2 class="section__title" id="testimonials-title">Ils utilisent BATITRAVOO</h2>
            </div>
            <div class="testimonials-grid">
                <article class="testimonial">
                    <figure class="testimonial__img">
                        <img src="{{ asset('images/Rectangle 29.png') }}" alt="" width="400" height="225" loading="lazy">
                    </figure>
                    <div class="testimonial__body">
                        <blockquote class="testimonial__quote">
                            En quelques heures, j'ai reçu plusieurs propositions d'artisans sérieux et lancé mes travaux dans de bonnes conditions.
                        </blockquote>
                    </div>
                    <footer class="testimonial__foot">
                        <img class="testimonial__avatar" src="{{ asset('images/Ellipse 7.png') }}" alt="Jean Marc Kouassi" width="44" height="44" loading="lazy">
                        <div>
                            <cite class="testimonial__name">Jean Marc Kouassi</cite>
                            <span class="testimonial__role">Particulier</span>
                            <div class="testimonial__stars" aria-label="5 sur 5">★★★★★</div>
                        </div>
                    </footer>
                </article>
                <article class="testimonial">
                    <figure class="testimonial__img">
                        <img src="{{ asset('images/Rectangle 29 (1).png') }}" alt="" width="400" height="225" loading="lazy">
                    </figure>
                    <div class="testimonial__body">
                        <blockquote class="testimonial__quote">
                            J'ai posté mon projet de rénovation et reçu des devis précis de plusieurs artisans qualifiés, prêts à démarrer.
                        </blockquote>
                    </div>
                    <footer class="testimonial__foot">
                        <img class="testimonial__avatar" src="{{ asset('images/Ellipse 7 (1).png') }}" alt="Mariam Tapé" width="44" height="44" loading="lazy">
                        <div>
                            <cite class="testimonial__name">Mariam Tapé</cite>
                            <span class="testimonial__role">Particulier</span>
                            <div class="testimonial__stars" aria-label="5 sur 5">★★★★★</div>
                        </div>
                    </footer>
                </article>
                <article class="testimonial">
                    <figure class="testimonial__img">
                        <img src="{{ asset('images/Rectangle 29 (2).png') }}" alt="" width="400" height="225" loading="lazy">
                    </figure>
                    <div class="testimonial__body">
                        <blockquote class="testimonial__quote">
                            Besoin urgent d'un plombier : un professionnel compétent m'a été proposé en moins d'une journée via la plateforme.
                        </blockquote>
                    </div>
                    <footer class="testimonial__foot">
                        <img class="testimonial__avatar" src="{{ asset('images/Ellipse 7 (2).png') }}" alt="Carol Zabré" width="44" height="44" loading="lazy">
                        <div>
                            <cite class="testimonial__name">Carol Zabré</cite>
                            <span class="testimonial__role">Particulier</span>
                            <div class="testimonial__stars" aria-label="5 sur 5">★★★★★</div>
                        </div>
                    </footer>
                </article>
            </div>
        </div>
    </section>

    <section class="cta-block section-reveal" id="inscription" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta-block__inner">
                <h2 class="cta-block__title" id="cta-title">Intégrez l'écosystème BATITRAVOO</h2>
                <p class="cta-block__text">
                    Entreprises, artisans, fournisseurs ou particuliers — créez votre compte et accédez aux outils métier du bâtiment.
                </p>
                <div class="cta-block__actions">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn--primary">Créer un compte</a>
                    @else
                        <a href="#inscription" class="btn btn--primary">Créer un compte</a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn--light">Se connecter</a>
                    @else
                        <a href="#connexion" class="btn btn--light">Se connecter</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
