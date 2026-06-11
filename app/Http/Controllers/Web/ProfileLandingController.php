<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProfileLandingController extends Controller
{
    public function particulier(): View
    {
        return $this->render('particulier');
    }

    public function artisan(): View
    {
        return $this->render('artisan');
    }

    public function fournisseur(): View
    {
        return $this->render('fournisseur');
    }

    public function entrepriseBtp(): View
    {
        return $this->render('entreprise_btp');
    }

    private function render(string $key): View
    {
        $profile = self::profiles()[$key] ?? null;
        if ($profile === null) {
            abort(404);
        }

        return view('vitrine.profile', ['profile' => $profile]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function profiles(): array
    {
        return [
            'particulier' => [
                'slug' => 'particulier',
                'num' => '03 — Particulier',
                'title' => 'Espace particulier',
                'icon' => 'images/image 4.png',
                'lead' => 'Trouvez des prestataires fiables, comparez les devis et suivez vos travaux depuis un espace sécurisé.',
                'benefits' => [
                    'Publiez vos besoins de rénovation ou construction',
                    'Recevez et comparez plusieurs devis détaillés',
                    'Échangez avec artisans et entreprises BTP via la messagerie',
                    'Suivez l’avancement de vos demandes et validations',
                ],
                'register_profil' => 'particulier',
            ],
            'artisan' => [
                'slug' => 'artisan',
                'num' => '02 — Artisan',
                'title' => 'Espace artisan',
                'icon' => 'images/image 3.png',
                'lead' => 'Accédez aux opportunités de chantier, proposez vos devis et valorisez votre vitrine professionnelle.',
                'benefits' => [
                    'Consultez les besoins publiés par particuliers et entreprises',
                    'Proposez des devis et candidatures ciblées',
                    'Gérez votre carte de visite et vos services',
                    'Développez votre visibilité dans l’annuaire BTP',
                ],
                'register_profil' => 'artisan',
            ],
            'fournisseur' => [
                'slug' => 'fournisseur',
                'num' => '04 — Fournisseur',
                'title' => 'Espace fournisseur',
                'icon' => 'images/image 5.png',
                'lead' => 'Diffusez votre catalogue matériaux, traitez commandes et devis auprès d’une clientèle BTP qualifiée.',
                'benefits' => [
                    'Publiez et gérez votre catalogue produits',
                    'Recevez des commandes depuis la marketplace',
                    'Répondez aux demandes de devis matériaux',
                    'Gagnez en visibilité auprès des professionnels du bâtiment',
                ],
                'register_profil' => 'entreprise_fournisseur',
            ],
            'entreprise_btp' => [
                'slug' => 'entreprise-btp',
                'num' => '01 — BTP',
                'title' => 'Espace entreprise BTP',
                'icon' => 'images/image 2.png',
                'lead' => 'Publiez vos besoins de chantier, recrutez des équipes et pilotez candidatures et devis en un seul endroit.',
                'benefits' => [
                    'Diffusez vos appels d’offres et besoins de recrutement',
                    'Recevez des candidatures d’artisans qualifiés',
                    'Gérez devis, validations et échanges projet',
                    'Accédez au catalogue fournisseurs intégré',
                ],
                'register_profil' => 'entrepreneur_batiment',
            ],
        ];
    }
}
