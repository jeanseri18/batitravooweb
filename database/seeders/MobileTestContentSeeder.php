<?php

namespace Database\Seeders;

use App\Models\Besoin;
use App\Models\Candidature;
use App\Models\Category;
use App\Models\Devis;
use App\Models\Message;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contenu de test lié aux comptes {@see MobileTestProfileUsersSeeder} (besoins, services,
 * candidatures, devis, produits, messages). Idempotent (clés stables / préfixe corps message).
 *
 * Exécuter après {@see MobileTestProfileUsersSeeder} et {@see CatalogSeeder} :
 * `php artisan db:seed --class=MobileTestContentSeeder`
 *
 * Les commandes fournisseur côté API / mobile sont des lignes [devis] avec [user_id] =
 * l’identifiant du fournisseur (prestataire). Voir les jeux « MOB-FOUR-* » ci‑dessous.
 */
class MobileTestContentSeeder extends Seeder
{
    private const BODY_PREFIX = '[MOBILE_TEST_SEED]';

    public function run(): void
    {
        $particulier = User::query()->where('email', 'mobile.particulier@batitravoo.test')->first();
        $artisan = User::query()->where('email', 'mobile.artisan@batitravoo.test')->first();
        $fournisseur = User::query()->where('email', 'mobile.fournisseur@batitravoo.test')->first();
        $batiment = User::query()->where('email', 'mobile.batiment@batitravoo.test')->first();

        if (! $particulier || ! $artisan || ! $fournisseur || ! $batiment) {
            $this->command?->warn('MobileTestContentSeeder : exécutez d’abord MobileTestProfileUsersSeeder.');

            return;
        }

        $catMateriaux = Category::query()->where('slug', 'materiaux')->first();
        $catEquipement = Category::query()->where('slug', 'equipement')->first();
        $catServices = Category::query()->where('slug', 'services-generaux')->first();

        $this->purgeSeedMessages([$particulier->id, $artisan->id, $fournisseur->id, $batiment->id]);

        // ——— Besoins (BTP + particulier) ———
        $besoinBtp1 = Besoin::query()->updateOrCreate(
            [
                'user_id' => $batiment->id,
                'title' => '[MOBILE] Terrassement accès chantier',
            ],
            [
                'budget' => '4 500 000 FCFA',
                'start_label' => 'Début : 1/6/2026',
                'place' => 'Abidjan — Cocody',
                'description' => 'Profil recherché : conducteur travaux / terrassement. Seed tests mobile.',
                'duration' => 'Du 1/6/2026 au 30/7/2026',
                'short_date' => 'Fin : 30/7/2026',
                'candidature_count' => 0,
                'status' => 'open',
                'image_path' => null,
            ]
        );

        $besoinBtp2 = Besoin::query()->updateOrCreate(
            [
                'user_id' => $batiment->id,
                'title' => '[MOBILE] Cloisons plaque de plâtre',
            ],
            [
                'budget' => 'À partir de 1 200 000 FCFA',
                'start_label' => 'Début : 15/5/2026',
                'place' => 'Plateau, Abidjan',
                'description' => 'Profil recherché : plaquiste. Seed tests mobile.',
                'duration' => 'Du 15/5/2026 au 15/6/2026',
                'short_date' => 'Fin : 15/6/2026',
                'candidature_count' => 0,
                'status' => 'open',
                'image_path' => null,
            ]
        );

        $besoinPart = Besoin::query()->updateOrCreate(
            [
                'user_id' => $particulier->id,
                'title' => '[MOBILE] Réparation fuite salle de bain',
            ],
            [
                'budget' => 'Sur devis',
                'start_label' => 'Début : 20/5/2026',
                'place' => 'Marcory',
                'description' => 'Profil recherché : plombier. Seed tests mobile.',
                'duration' => 'Du 20/5/2026 au 25/5/2026',
                'short_date' => 'Fin : 25/5/2026',
                'candidature_count' => 0,
                'status' => 'open',
                'image_path' => null,
            ]
        );

        // ——— Services (artisan + BTP) ———
        Service::query()->updateOrCreate(
            ['slug' => 'mobile-test-artisan-depannage-urgence'],
            [
                'user_id' => $artisan->id,
                'category_id' => $catServices?->id,
                'title' => '[MOBILE] Dépannage plomberie urgent',
                'description' => 'Intervention sous 24h — compte test artisan mobile.',
                'location' => 'Abidjan',
                'image_url' => null,
                'image_path' => null,
                'service_kind' => 'artisan',
                'price_variables' => true,
                'price_fixed_label' => '35 000 FCFA',
                'rating' => 4.5,
                'review_count' => 12,
                'status' => 'approved',
            ]
        );

        Service::query()->updateOrCreate(
            ['slug' => 'mobile-test-artisan-installation-sanitaire'],
            [
                'user_id' => $artisan->id,
                'category_id' => $catServices?->id,
                'title' => '[MOBILE] Installation sanitaire',
                'description' => 'Installation sanitaire — seed mobile.',
                'location' => 'Grand-Bassam',
                'image_url' => null,
                'image_path' => null,
                'service_kind' => 'artisan',
                'price_variables' => false,
                'price_fixed_label' => '180 000 FCFA',
                'rating' => 0,
                'review_count' => 0,
                'status' => 'approved',
            ]
        );

        Service::query()->updateOrCreate(
            ['slug' => 'mobile-test-btp-coordination-chantier'],
            [
                'user_id' => $batiment->id,
                'category_id' => $catServices?->id,
                'title' => '[MOBILE] Coordination de chantier',
                'description' => 'Prestation entrepreneur bâtiment — seed mobile.',
                'location' => 'Abidjan & périphérie',
                'image_url' => null,
                'image_path' => null,
                'service_kind' => 'entrepreneur',
                'price_variables' => true,
                'price_fixed_label' => 'À partir de 450 000 FCFA',
                'rating' => 4.2,
                'review_count' => 7,
                'status' => 'approved',
            ]
        );

        // ——— Produits (fournisseur) ———
        Product::query()->updateOrCreate(
            ['slug' => 'mobile-test-ciment-42-5'],
            [
                'user_id' => $fournisseur->id,
                'category_id' => $catMateriaux?->id,
                'title' => '[MOBILE] Ciment CPJ 42,5 (sac 50 kg)',
                'description' => 'Produit approuvé — seed compte fournisseur mobile.',
                'price_amount' => 6500,
                'stock_units' => 500,
                'views_count' => 42,
                'status' => 'approved',
                'image_path' => null,
            ]
        );

        Product::query()->updateOrCreate(
            ['slug' => 'mobile-test-brouette-chantier'],
            [
                'user_id' => $fournisseur->id,
                'category_id' => $catEquipement?->id,
                'title' => '[MOBILE] Brouette renforcée 100 L',
                'description' => 'Produit en attente validation admin — seed mobile.',
                'price_amount' => 18500,
                'stock_units' => 20,
                'views_count' => 5,
                'status' => 'pending',
                'image_path' => null,
            ]
        );

        Product::query()->updateOrCreate(
            ['slug' => 'mobile-test-seau-gradue'],
            [
                'user_id' => $fournisseur->id,
                'category_id' => $catEquipement?->id,
                'title' => '[MOBILE] Seau gradué chantier',
                'description' => 'Brouillon — seed mobile.',
                'price_amount' => 2500,
                'stock_units' => 100,
                'views_count' => 0,
                'status' => 'draft',
                'image_path' => null,
            ]
        );

        // ——— Devis (émis par le compte BTP, client = particulier quand pertinent) ———
        $devisRows = [
            [
                'ref' => 'MOB-DEV-001',
                'title' => '[MOBILE] Gros œuvre extension',
                'client_name' => $particulier->name,
                'place' => 'Cocody',
                'contact' => $particulier->phone ?? $particulier->email,
                'status' => 'en_cours',
                'client_user_id' => $particulier->id,
            ],
            [
                'ref' => 'MOB-DEV-002',
                'title' => '[MOBILE] Second œuvre peinture',
                'client_name' => 'Client externe seed',
                'place' => 'Marcory',
                'contact' => '+22501000000',
                'status' => 'non_traite',
                'client_user_id' => null,
            ],
            [
                'ref' => 'MOB-DEV-003',
                'title' => '[MOBILE] Plomberie bâtiment B',
                'client_name' => $particulier->name,
                'place' => 'Plateau',
                'contact' => $particulier->email,
                'status' => 'valide',
                'client_user_id' => $particulier->id,
            ],
        ];

        foreach ($devisRows as $i => $row) {
            Devis::query()->updateOrCreate(
                [
                    'user_id' => $batiment->id,
                    'order_reference' => $row['ref'],
                ],
                [
                    'title' => $row['title'],
                    'client_name' => $row['client_name'],
                    'place' => $row['place'],
                    'contact' => $row['contact'],
                    'status' => $row['status'],
                    'client_user_id' => $row['client_user_id'],
                    'processed_at' => in_array($row['status'], ['valide', 'envoye', 'rejete'], true)
                        ? Carbon::now()->subDays(3 + $i)
                        : null,
                    'line_items' => [
                        ['name' => 'Main d’œuvre', 'qty' => 1, 'unit' => 'forfait', 'total' => 320000],
                        ['name' => 'Matériaux', 'qty' => 1, 'unit' => 'lot', 'total' => 195000],
                    ],
                    'notes' => 'Ligne seed MobileTestContentSeeder.',
                ]
            );
        }

        // ——— Devis reçus par le fournisseur (hors commandes catalogue MOB-FOUR-*) ———
        $devisFournisseurRows = [
            [
                'ref' => 'MOB-DEVIS-FOUR-001',
                'title' => '[MOBILE] Demande devis — lot matériaux Cocody',
                'client_name' => $particulier->name,
                'place' => 'Cocody, Abidjan',
                'contact' => $particulier->phone ?? $particulier->email,
                'status' => 'non_traite',
                'client_user_id' => $particulier->id,
                'days_ago' => 2,
                'lines' => [
                    ['label' => '[MOBILE] Ciment CPJ 42,5 (sac 50 kg)', 'qty' => 30, 'unit_price_fcfa' => 0],
                    ['label' => 'Livraison chantier', 'qty' => 1, 'unit_price_fcfa' => 0],
                ],
            ],
            [
                'ref' => 'MOB-DEVIS-FOUR-002',
                'title' => '[MOBILE] Demande devis — équipement BTP',
                'client_name' => $batiment->company_name ?? $batiment->name,
                'place' => 'Plateau',
                'contact' => $batiment->phone ?? $batiment->email,
                'status' => 'envoye',
                'client_user_id' => $batiment->id,
                'days_ago' => 5,
                'lines' => [
                    ['label' => '[MOBILE] Brouette renforcée 100 L', 'qty' => 5, 'unit_price_fcfa' => 18500],
                    ['label' => '[MOBILE] Seau gradué chantier', 'qty' => 8, 'unit_price_fcfa' => 2500],
                ],
            ],
            [
                'ref' => 'MOB-PROP-FOUR-001',
                'title' => '[MOBILE] Proposition client externe — Marcory',
                'client_name' => 'Société Chantier Marcory SARL',
                'place' => 'Marcory',
                'contact' => '+2250700000001',
                'status' => 'en_cours',
                'client_user_id' => null,
                'days_ago' => 3,
                'lines' => [
                    ['label' => 'Fourniture consommables', 'qty' => 1, 'unit_price_fcfa' => 120000],
                ],
            ],
        ];

        foreach ($devisFournisseurRows as $row) {
            $at = Carbon::now()->subDays((int) $row['days_ago']);
            $processedAt = in_array($row['status'], ['valide', 'envoye', 'rejete'], true)
                ? $at->copy()->addDay()->toDateString()
                : null;

            $devis = Devis::query()->updateOrCreate(
                [
                    'user_id' => $fournisseur->id,
                    'order_reference' => $row['ref'],
                ],
                [
                    'title' => $row['title'],
                    'client_name' => $row['client_name'],
                    'place' => $row['place'],
                    'contact' => $row['contact'],
                    'status' => $row['status'],
                    'client_user_id' => $row['client_user_id'],
                    'processed_at' => $processedAt,
                    'line_items' => [
                        'source' => 'marketplace_request',
                        'lignes' => $row['lines'],
                    ],
                    'notes' => 'Devis seed MobileTestContentSeeder — écran « Mes devis » fournisseur.',
                ]
            );

            DB::table('devis')->where('id', $devis->id)->update([
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }

        // ——— Commandes reçues par le fournisseur mobile (devis : user_id = fournisseur) ———
        $cmdFournisseurRows = [
            [
                'ref' => 'MOB-FOUR-001',
                'title' => '[MOBILE] Matériaux gros œuvre — Cocody',
                'client_name' => $particulier->name,
                'place' => 'Cocody, Abidjan',
                'contact' => $particulier->phone ?? $particulier->email,
                'status' => 'en_cours',
                'client_user_id' => $particulier->id,
                'days_ago' => 1,
                'lines' => [
                    ['name' => '[MOBILE] Ciment CPJ 42,5 (sac 50 kg)', 'qty' => 20, 'unit' => 'sac', 'total' => 130000],
                    ['name' => 'Livraison chantier', 'qty' => 1, 'unit' => 'forfait', 'total' => 25000],
                ],
            ],
            [
                'ref' => 'MOB-FOUR-002',
                'title' => '[MOBILE] Équipement chantier — BTP',
                'client_name' => $batiment->company_name ?? $batiment->name,
                'place' => 'Plateau',
                'contact' => $batiment->phone ?? $batiment->contact_email ?? $batiment->email,
                'status' => 'non_traite',
                'client_user_id' => $batiment->id,
                'days_ago' => 2,
                'lines' => [
                    ['name' => '[MOBILE] Brouette renforcée 100 L', 'qty' => 3, 'unit' => 'unité', 'total' => 55500],
                ],
            ],
            [
                'ref' => 'MOB-FOUR-003',
                'title' => '[MOBILE] Fourniture petite quantité',
                'client_name' => $particulier->name,
                'place' => 'Marcory',
                'contact' => $particulier->email,
                'status' => 'valide',
                'client_user_id' => $particulier->id,
                'days_ago' => 4,
                'lines' => [
                    ['name' => '[MOBILE] Seau gradué chantier', 'qty' => 10, 'unit' => 'unité', 'total' => 25000],
                    ['name' => 'Frais de préparation commande', 'qty' => 1, 'unit' => 'forfait', 'total' => 5000],
                ],
            ],
            [
                'ref' => 'MOB-FOUR-004',
                'title' => '[MOBILE] Réapprovisionnement ciment',
                'client_name' => $batiment->company_name ?? $batiment->name,
                'place' => 'Yopougon — zone industrielle',
                'contact' => $batiment->manager_contact ?? $batiment->phone,
                'status' => 'valide',
                'client_user_id' => $batiment->id,
                'days_ago' => 6,
                'lines' => [
                    ['name' => '[MOBILE] Ciment CPJ 42,5 (sac 50 kg)', 'qty' => 100, 'unit' => 'sac', 'total' => 650000],
                ],
            ],
            [
                'ref' => 'MOB-FOUR-005',
                'title' => '[MOBILE] Commande express outillage',
                'client_name' => $particulier->name,
                'place' => 'Abidjan',
                'contact' => $particulier->phone ?? $particulier->email,
                'status' => 'envoye',
                'client_user_id' => $particulier->id,
                'days_ago' => 8,
                'lines' => [
                    ['name' => 'Lot outillage chantier (seed)', 'qty' => 1, 'unit' => 'lot', 'total' => 89000],
                ],
            ],
            [
                'ref' => 'MOB-FOUR-006',
                'title' => '[MOBILE] Commande mois précédent (tendance KPI)',
                'client_name' => $particulier->name,
                'place' => 'Cocody',
                'contact' => $particulier->phone ?? $particulier->email,
                'status' => 'valide',
                'client_user_id' => $particulier->id,
                'days_ago' => 40,
                'lines' => [
                    ['name' => '[MOBILE] Ciment CPJ 42,5 (sac 50 kg)', 'qty' => 15, 'unit' => 'sac', 'total' => 97500],
                ],
            ],
        ];

        foreach ($cmdFournisseurRows as $row) {
            $at = Carbon::now()->subDays((int) $row['days_ago']);
            $processedAt = in_array($row['status'], ['valide', 'envoye', 'rejete'], true)
                ? $at->copy()->addDay()->toDateString()
                : null;

            $devis = Devis::query()->updateOrCreate(
                [
                    'user_id' => $fournisseur->id,
                    'order_reference' => $row['ref'],
                ],
                [
                    'title' => $row['title'],
                    'client_name' => $row['client_name'],
                    'place' => $row['place'],
                    'contact' => $row['contact'],
                    'status' => $row['status'],
                    'client_user_id' => $row['client_user_id'],
                    'processed_at' => $processedAt,
                    'line_items' => $row['lines'],
                    'notes' => 'Commande seed MobileTestContentSeeder — compte mobile.fournisseur@batitravoo.test.',
                ]
            );

            DB::table('devis')->where('id', $devis->id)->update([
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }

        // ——— Candidatures + devis opportunité (artisan → besoins BTP / particulier) ———
        $this->seedArtisanBesoinCandidature(
            besoin: $besoinBtp1,
            artisan: $artisan,
            client: $batiment,
            candidatureStatus: 'recu',
            devisStatus: 'envoye',
            devisTitle: '[MOBILE] Devis terrassement — accès chantier',
            profession: 'Terrassement / VRD',
            postedDaysAgo: 5,
        );

        $this->seedArtisanBesoinCandidature(
            besoin: $besoinBtp2,
            artisan: $artisan,
            client: $batiment,
            candidatureStatus: 'recu',
            devisStatus: 'envoye',
            devisTitle: '[MOBILE] Devis cloisons plaque de plâtre',
            profession: 'Plomberie / second œuvre',
            postedDaysAgo: 2,
        );

        $this->seedArtisanBesoinCandidature(
            besoin: $besoinPart,
            artisan: $artisan,
            client: $particulier,
            candidatureStatus: 'recu',
            devisStatus: 'envoye',
            devisTitle: '[MOBILE] Devis réparation fuite',
            profession: 'Plomberie / second œuvre',
            postedDaysAgo: 3,
        );

        $this->refreshBesoinCandidatureCounts([$besoinBtp1, $besoinBtp2, $besoinPart]);

        // ——— Messages (aperçu messagerie) ———
        $this->seedMessage($particulier->id, $artisan->id, 'Bonjour, je souhaite un devis pour la fuite mentionnée dans mon besoin [MOBILE].');
        $this->seedMessage($artisan->id, $particulier->id, 'Bonjour, je peux passer demain en fin de matinée pour diagnostic.');
        $this->seedMessage($particulier->id, $fournisseur->id, 'Avez-vous le ciment [MOBILE] en stock pour retrait sur place ?');
        $this->seedMessage($fournisseur->id, $particulier->id, 'Oui, disponible à l’entrepôt — merci de confirmer quantité.');

        $this->command?->info('MobileTestContentSeeder : besoins, services, produits, devis, commandes fournisseur, candidatures, messages OK.');
    }

    /**
     * Candidature + devis lié (réf. BESOIN-{id}) comme en production (postulation artisan).
     */
    private function seedArtisanBesoinCandidature(
        Besoin $besoin,
        User $artisan,
        User $client,
        string $candidatureStatus,
        string $devisStatus,
        string $devisTitle,
        string $profession,
        int $postedDaysAgo = 2,
    ): void {
        $ref = 'BESOIN-'.$besoin->id;
        $postedAt = Carbon::now()->subDays($postedDaysAgo);
        $processedAt = in_array($devisStatus, ['valide', 'rejete'], true)
            ? $postedAt->copy()->addDay()->toDateString()
            : null;

        $devis = Devis::query()->updateOrCreate(
            [
                'user_id' => $artisan->id,
                'order_reference' => $ref,
            ],
            [
                'client_user_id' => $client->id,
                'title' => $devisTitle,
                'client_name' => $client->company_name ?? $client->name,
                'place' => $besoin->place,
                'contact' => $client->phone ?? $client->email,
                'status' => $devisStatus,
                'processed_at' => $processedAt,
                'line_items' => [
                    ['name' => 'Main d’œuvre', 'qty' => 1, 'unit' => 'forfait', 'total' => 450000],
                    ['name' => 'Fournitures', 'qty' => 1, 'unit' => 'lot', 'total' => 280000],
                ],
                'notes' => 'Devis seed MobileTestContentSeeder — validation BTP / particulier.',
            ]
        );

        DB::table('devis')->where('id', $devis->id)->update([
            'created_at' => $postedAt,
            'updated_at' => $postedAt,
        ]);

        Candidature::query()->updateOrCreate(
            [
                'besoin_id' => $besoin->id,
                'applicant_id' => $artisan->id,
            ],
            [
                'display_name' => $artisan->name,
                'profession' => $profession,
                'status' => $candidatureStatus,
                'posted_at' => $postedAt,
                'message' => 'Proposition de devis transmise (n° '.$devis->id.').',
            ]
        );

        if ($candidatureStatus === 'accepte' || $devisStatus === 'valide') {
            $besoin->update(['status' => 'in_progress']);
        } elseif ($besoin->status === 'in_progress' && $candidatureStatus === 'recu' && $devisStatus === 'envoye') {
            $besoin->update(['status' => 'open']);
        }
    }

    /**
     * @param  list<Besoin>  $besoins
     */
    private function refreshBesoinCandidatureCounts(array $besoins): void
    {
        foreach ($besoins as $b) {
            $n = Candidature::query()->where('besoin_id', $b->id)->count();
            $b->update(['candidature_count' => $n]);
        }
    }

    /**
     * @param  array<int>  $userIds
     */
    private function purgeSeedMessages(array $userIds): void
    {
        Message::query()
            ->where(function ($q) use ($userIds) {
                $q->whereIn('sender_id', $userIds)
                    ->orWhereIn('receiver_id', $userIds);
            })
            ->where('body', 'like', self::BODY_PREFIX.'%')
            ->delete();
    }

    private function seedMessage(int $senderId, int $receiverId, string $body): void
    {
        Message::query()->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'body' => self::BODY_PREFIX.' '.$body,
            'read_at' => null,
            'attachment_path' => null,
            'attachment_original_name' => null,
        ]);
    }
}
