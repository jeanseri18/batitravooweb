<footer class="site-footer section-reveal" role="contentinfo">
    <div class="site-footer__main">
        <div>
            <p class="site-footer__brand">BATITRAVOO</p>
            <p class="site-footer__tagline">Plateforme professionnelle du bâtiment en Côte d'Ivoire.</p>
            <ul class="site-footer__social" style="margin-top:1rem;">
                <li><a href="#" rel="noopener noreferrer">Facebook</a></li>
                <li><a href="#" rel="noopener noreferrer">LinkedIn</a></li>
                <li><a href="#" rel="noopener noreferrer">Instagram</a></li>
                <li><a href="#" rel="noopener noreferrer">WhatsApp</a></li>
            </ul>
        </div>
        <nav aria-label="Navigation">
            <p class="site-footer__col-title">Navigation</p>
            <ul class="site-footer__links">
                <li><a href="{{ url('/') }}#accueil">Accueil</a></li>
                <li><a href="{{ route('vitrine.annuaire') }}">Annuaire</a></li>
                <li><a href="{{ url('/') }}#solution">Solution</a></li>
                <li><a href="{{ url('/') }}#pour-qui">Métiers</a></li>
                <li><a href="{{ url('/') }}#fonctionnalites">Services</a></li>
                <li><a href="{{ url('/') }}#temoignages">Références</a></li>
            </ul>
        </nav>
        <nav aria-label="Profils utilisateurs">
            <p class="site-footer__col-title">Profils</p>
            <ul class="site-footer__links">
                <li><a href="{{ route('vitrine.entreprise_btp') }}">Entreprise BTP</a></li>
                <li><a href="{{ route('vitrine.fournisseur') }}">Fournisseur</a></li>
                <li><a href="{{ route('vitrine.artisan') }}">Artisan</a></li>
                <li><a href="{{ route('vitrine.particulier') }}">Particulier</a></li>
            </ul>
        </nav>
        <nav aria-label="Support">
            <p class="site-footer__col-title">Support</p>
            <ul class="site-footer__links">
                <li><a href="{{ url('/admin/login') }}">Espace admin</a></li>
                <li><a href="{{ route('vitrine.contact') }}">Contact</a></li>
                <li><a href="{{ route('vitrine.help_center') }}">Centre d'aide</a></li>
                <li><a href="{{ route('vitrine.faq') }}">FAQ</a></li>
                <li><a href="{{ route('vitrine.terms') }}">Conditions d'utilisation</a></li>
                <li><a href="{{ route('vitrine.privacy') }}">Politique de confidentialité</a></li>
            </ul>
        </nav>
    </div>
    <div class="site-footer__bottom">
        <p class="site-footer__copyright">© {{ date('Y') }} BATITRAVOO — Tous droits réservés</p>
    </div>
</footer>
