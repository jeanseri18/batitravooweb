<?php

namespace Database\Seeders;

use App\Models\Besoin;
use App\Models\Candidature;
use App\Models\Category;
use App\Models\Devis;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Données de démo pour alimenter tableau de bord, rapports et listes admin.
 * Idempotent : s’appuie sur des e-mails uniques (updateOrCreate).
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $catMateriaux = Category::query()->where('slug', 'materiaux')->first();
        $catEquipement = Category::query()->where('slug', 'equipement')->first();
        $catServices = Category::query()->where('slug', 'services-generaux')->first();

        $entrepreneur = $this->user(
            'entrepreneur@demo.batitravoo',
            'Kouassi Patrimoine BTP',
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            '+225 07 11 22 01',
            'Kouassi Patrimoine',
            'Abidjan, Cocody',
            now()->subMonths(5)->startOfMonth()->addDays(5)
        );

        $fournisseur = $this->user(
            'fournisseur@demo.batitravoo',
            'SARL Matériaux Pro',
            User::PROFILE_ENTREPRISE_FOURNISSEUR,
            '+225 07 11 22 02',
            'Matériaux Pro CI',
            'Zone industrielle Yopougon',
            now()->subMonths(4)->startOfMonth()->addDays(12)
        );

        $artisan1 = $this->user(
            'artisan.plomberie@demo.batitravoo',
            'Yao Plomberie',
            User::PROFILE_ARTISAN,
            '+225 07 11 22 03',
            'Yao Plomberie SARL',
            null,
            now()->subMonths(3)->startOfMonth()->addDays(3)
        );

        $artisan2 = $this->user(
            'artisan.electricite@demo.batitravoo',
            'Élec & Fils',
            User::PROFILE_ARTISAN,
            '+225 07 11 22 04',
            null,
            null,
            now()->subMonths(2)->startOfMonth()->addDays(20)
        );

        $particulier = $this->user(
            'particulier@demo.batitravoo',
            'Mariam Diallo',
            User::PROFILE_PARTICULIER,
            '+225 07 11 22 05',
            null,
            null,
            now()->subMonths(1)->startOfMonth()->addDays(8)
        );

        $extra = $this->user(
            'btp.extra@demo.batitravoo',
            'BTP Horizon',
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            '+225 07 11 22 06',
            'BTP Horizon',
            'Bouaké',
            now()->subDays(12)
        );

        $products = [
            ['title' => 'Ciment Portland', 'price' => 5000, 'stock' => 120000, 'views' => 2300, 'status' => 'approved', 'cat' => $catMateriaux],
            ['title' => 'Brouette renforcée', 'price' => 15000, 'stock' => 45, 'views' => 21000, 'status' => 'approved', 'cat' => $catEquipement],
            ['title' => 'Perceuse visseuse', 'price' => 45000, 'stock' => 30, 'views' => 1200, 'status' => 'pending', 'cat' => $catEquipement],
            ['title' => 'Malaxeur 180L', 'price' => 50000, 'stock' => 0, 'views' => 2300, 'status' => 'pending', 'cat' => $catEquipement],
            ['title' => 'Sable 0-4', 'price' => 8000, 'stock' => 95, 'views' => 890, 'status' => 'approved', 'cat' => $catMateriaux],
            ['title' => 'Tôles galvanisées', 'price' => 12000, 'stock' => 200, 'views' => 450, 'status' => 'draft', 'cat' => $catMateriaux],
        ];

        foreach ($products as $row) {
            $slug = Str::slug($row['title']).'-demo';
            Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $fournisseur->id,
                    'category_id' => $row['cat']?->id,
                    'title' => $row['title'],
                    'description' => 'Article catalogue démo — aligné écran fournisseur.',
                    'price_amount' => $row['price'],
                    'stock_units' => $row['stock'],
                    'views_count' => $row['views'] ?? 0,
                    'status' => $row['status'],
                ]
            );
        }

        $services = [
            [
                'title' => 'Installation sanitaire complète',
                'kind' => 'artisan',
                'location' => 'Abidjan',
                'rating' => 4.6,
                'reviews' => 120,
                'status' => 'approved',
                'price_var' => true,
                'price_label' => null,
                'user' => $artisan1,
                'cat' => $catServices,
            ],
            [
                'title' => 'Mise aux normes tableau électrique',
                'kind' => 'artisan',
                'location' => 'Grand-Bassam',
                'rating' => 4.8,
                'reviews' => 89,
                'status' => 'pending',
                'price_var' => false,
                'price_label' => 'À partir de 85 000 FCFA',
                'user' => $artisan2,
                'cat' => $catServices,
            ],
            [
                'title' => 'Coordination gros œuvre & finitions',
                'kind' => 'entrepreneur',
                'location' => 'Abidjan — tous secteurs',
                'rating' => 4.5,
                'reviews' => 34,
                'status' => 'approved',
                'price_var' => true,
                'price_label' => '',
                'user' => $entrepreneur,
                'cat' => $catServices,
            ],
            [
                'title' => 'Étanchéité toiture terrasse',
                'kind' => 'artisan',
                'location' => 'Yamoussoukro',
                'rating' => 4.2,
                'reviews' => 56,
                'status' => 'rejected',
                'price_var' => false,
                'price_label' => 'Forfait selon surface',
                'user' => $artisan1,
                'cat' => $catServices,
            ],
            [
                'title' => 'Second œuvre peinture',
                'kind' => 'entrepreneur',
                'location' => 'San-Pédro',
                'rating' => 4.0,
                'reviews' => 12,
                'status' => 'pending',
                'price_var' => true,
                'price_label' => null,
                'user' => $extra,
                'cat' => $catServices,
            ],
        ];

        foreach ($services as $row) {
            $slug = Str::slug($row['title']).'-svc-demo';
            Service::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $row['user']->id,
                    'category_id' => $row['cat']?->id,
                    'title' => $row['title'],
                    'description' => 'Prestation démo — modèles ArtisanPublishedService / BatimentServiceData.',
                    'location' => $row['location'],
                    'image_url' => null,
                    'image_path' => null,
                    'service_kind' => $row['kind'],
                    'price_variables' => $row['price_var'],
                    'price_fixed_label' => $row['price_label'] ?: null,
                    'rating' => $row['rating'],
                    'review_count' => $row['reviews'],
                    'status' => $row['status'],
                ]
            );
        }

        $devisRows = [
            ['title' => 'Extension villa — gros œuvre', 'client' => 'M. Koné', 'ref' => 'N°78HGG', 'place' => 'Cocody Riviera', 'contact' => '+225 05 00 11 22', 'status' => 'en_cours'],
            ['title' => 'Réfection toiture', 'client' => 'Mme Traoré', 'ref' => 'N°89KLM', 'place' => 'Marcory', 'contact' => 'traore@mail.ci', 'status' => 'non_traite'],
            ['title' => 'Clôture + portail', 'client' => 'SCI Les Palmiers', 'ref' => 'N°90ZZ1', 'place' => 'Bingerville', 'contact' => '+225 07 99 88 77', 'status' => 'envoye'],
            ['title' => 'Plomberie sanitaire bât. B', 'client' => 'Promoteur Horizon', 'ref' => 'N°91PLB', 'place' => 'Plateau', 'contact' => 'contact@horizon.ci', 'status' => 'valide'],
            ['title' => 'Électricité sous-sol', 'client' => 'Entrepôt Central', 'ref' => 'N°92ELC', 'place' => 'Yopougon', 'contact' => '+225 01 02 03 04', 'status' => 'rejete'],
            ['title' => 'Carrelage halls', 'client' => 'Résidence Émeraude', 'ref' => 'N°93CAR', 'place' => 'II Plateaux', 'contact' => 'em@résidence.ci', 'status' => 'en_cours'],
        ];

        foreach ($devisRows as $i => $row) {
            Devis::query()->updateOrCreate(
                [
                    'user_id' => $entrepreneur->id,
                    'order_reference' => $row['ref'],
                ],
                [
                    'title' => $row['title'],
                    'client_name' => $row['client'],
                    'place' => $row['place'],
                    'contact' => $row['contact'],
                    'status' => $row['status'],
                    'processed_at' => in_array($row['status'], ['valide', 'envoye', 'rejete'], true)
                        ? Carbon::now()->subDays(2 + $i)
                        : null,
                    'line_items' => [
                        ['name' => 'Main d’œuvre', 'qty' => 1, 'unit' => 'forfait', 'total' => 250000],
                        ['name' => 'Matériaux', 'qty' => 1, 'unit' => 'lot', 'total' => 180000],
                    ],
                ]
            );
        }

        $besoin1 = Besoin::query()->updateOrCreate(
            ['user_id' => $entrepreneur->id, 'title' => 'Charpente métallique entrepôt'],
            [
                'budget' => '12 000 000 – 15 000 000 FCFA',
                'start_label' => 'Début 15 mai 2026',
                'place' => 'Zone industrielle Abidjan',
                'description' => 'Recherche équipe pour charpente et bardage — délai 6 semaines.',
                'duration' => '6 semaines',
                'short_date' => '10 avril 2026',
                'candidature_count' => 0,
                'status' => 'open',
            ]
        );

        $besoin2 = Besoin::query()->updateOrCreate(
            ['user_id' => $extra->id, 'title' => 'Rénovation façade immeuble'],
            [
                'budget' => 'Budget négocié sur devis',
                'start_label' => 'Dès signature',
                'place' => 'Bouaké centre',
                'description' => 'Ravalement + isolation — besoin entreprise qualifiée.',
                'duration' => '3 mois',
                'short_date' => '2 avril 2026',
                'candidature_count' => 0,
                'status' => 'in_progress',
            ]
        );

        $besoin3 = Besoin::query()->updateOrCreate(
            ['user_id' => $entrepreneur->id, 'title' => 'Terrassement lotissement'],
            [
                'budget' => '8 000 000 FCFA',
                'start_label' => 'Mai 2026',
                'place' => 'Grand-Bassam',
                'description' => 'Terrassement + voirie — appel d’offres ouvert.',
                'duration' => '8 semaines',
                'short_date' => '28 mars 2026',
                'candidature_count' => 0,
                'status' => 'closed',
            ]
        );

        $cands = [
            [$besoin1, $artisan1, 'recu', 'Yao Plomberie', 'Plomberie / second œuvre'],
            [$besoin1, $artisan2, 'accepte', 'Élec & Fils', 'Électricité'],
            [$besoin1, $particulier, 'rejete', 'Mariam Diallo', 'Particulier — orientation'],
            [$besoin2, $artisan1, 'recu', 'Yao Plomberie', 'Plomberie'],
            [$besoin2, $artisan2, 'recu', 'Élec & Fils', 'Électricité'],
            [$besoin3, $artisan1, 'rejete', 'Yao Plomberie', 'Coordination'],
        ];

        foreach ($cands as $c) {
            /** @var Besoin $b */
            [$b, $applicant, $status, $display, $prof] = $c;
            $postedAt = now()->subDays(rand(1, 14));
            $besoinOwner = User::query()->find($b->user_id);
            $devis = null;

            if ($besoinOwner && in_array($applicant->profile_type, [User::PROFILE_ARTISAN], true)) {
                $devisStatus = match ($status) {
                    'accepte' => 'valide',
                    'rejete' => 'rejete',
                    default => 'envoye',
                };
                $devis = Devis::query()->updateOrCreate(
                    [
                        'user_id' => $applicant->id,
                        'order_reference' => 'BESOIN-'.$b->id,
                    ],
                    [
                        'client_user_id' => $besoinOwner->id,
                        'title' => 'Devis — '.$b->title,
                        'client_name' => $besoinOwner->company_name ?? $besoinOwner->name,
                        'place' => $b->place,
                        'contact' => $besoinOwner->phone ?? $besoinOwner->email,
                        'status' => $devisStatus,
                        'processed_at' => in_array($devisStatus, ['valide', 'rejete'], true)
                            ? $postedAt->copy()->addDay()->toDateString()
                            : null,
                        'line_items' => [
                            ['name' => 'Main d’œuvre', 'qty' => 1, 'unit' => 'forfait', 'total' => 380000],
                        ],
                        'notes' => 'Devis seed DemoContentSeeder.',
                    ]
                );
            }

            Candidature::query()->updateOrCreate(
                [
                    'besoin_id' => $b->id,
                    'applicant_id' => $applicant->id,
                ],
                [
                    'display_name' => $display,
                    'profession' => $prof,
                    'status' => $status,
                    'posted_at' => $postedAt,
                    'message' => $devis !== null
                        ? 'Proposition de devis transmise (n° '.$devis->id.').'
                        : 'Candidature démo — alignement écran candidature entrepreneur.',
                ]
            );
        }

        foreach ([$besoin1, $besoin2, $besoin3] as $b) {
            $n = Candidature::query()->where('besoin_id', $b->id)->count();
            $b->update(['candidature_count' => $n]);
        }
    }

    private function user(
        string $email,
        string $name,
        string $profile,
        string $phone,
        ?string $company,
        ?string $address,
        Carbon $createdAt
    ): User {
        $u = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
                'profile_type' => $profile,
                'phone' => $phone,
                'is_active' => true,
                'company_name' => $company,
                'company_address' => $address,
            ]
        );
        $extras = ['created_at' => $createdAt, 'updated_at' => now()];
        if ($profile === User::PROFILE_ARTISAN || $profile === User::PROFILE_ENTREPRENEUR_BATIMENT
            || $profile === User::PROFILE_ENTREPRISE_FOURNISSEUR) {
            $extras['profile_completed_at'] = $createdAt;
            $extras['profile_validation_status'] = User::VALIDATION_APPROVED;
            $extras['profile_validated_at'] = $createdAt;
        }
        $u->forceFill($extras)->saveQuietly();

        return $u->fresh();
    }
}
