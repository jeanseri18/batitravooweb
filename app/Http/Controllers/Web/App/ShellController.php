<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\DevisScopeService;
use App\Services\Web\MeApiBridge;
use App\Services\Web\SupplierMarketplaceCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShellController extends Controller
{
    public function home(Request $request): View
    {
        return $this->render($request, 'home');
    }

    public function dashboard(Request $request): View
    {
        return $this->render($request, 'dashboard_tab');
    }

    public function messages(Request $request): View
    {
        return $this->render($request, 'messages');
    }

    public function settings(Request $request): View
    {
        return $this->render($request, 'settings');
    }

    public function profile(Request $request): View
    {
        return $this->render($request, 'profile');
    }

    public function profilePassword(Request $request): View
    {
        return $this->render($request, 'profile_password');
    }

    public function profileLocation(Request $request): View
    {
        $slug = (string) $request->segment(2);
        abort_unless(in_array($slug, ['batiment', 'fournisseur'], true), 404);

        return $this->render($request, 'profile_location');
    }

    public function serviceClient(Request $request): View
    {
        return $this->render($request, 'service_client');
    }

    public function supplierCart(Request $request): View|RedirectResponse
    {
        $slug = (string) $request->segment(2);
        if ($slug === 'fournisseur') {
            return redirect()
                ->route('app.fournisseur.marketplace', ['tab' => 'produits'])
                ->with('status', 'Le panier n’est pas utilisé pour ce profil (aligné application mobile).');
        }

        return $this->render($request, 'supplier_cart', [
            'cartLines' => app(SupplierMarketplaceCart::class)->lines($request),
        ]);
    }

    public function marketplace(Request $request): View
    {
        return $this->render($request, 'marketplace');
    }

    public function devis(Request $request): View
    {
        return $this->render($request, 'devis');
    }

    public function fournisseurOrders(Request $request): View
    {
        abort_unless($request->segment(2) === 'fournisseur', 404);

        return $this->render($request, 'fournisseur_orders');
    }

    public function devisShow(Request $request, Devis $devis): View
    {
        return $this->render($request, 'devis_show', ['routeDevis' => $devis]);
    }

    public function devisCreate(Request $request): View|RedirectResponse
    {
        $slug = (string) $request->segment(2);
        $user = $request->user();

        if ($user?->profile_type === User::PROFILE_ARTISAN) {
            return redirect()
                ->route('app.'.$slug.'.marketplace', ['tab' => 'besoins'])
                ->with('status', 'Pour un devis lié à un besoin, ouvrez la fiche besoin dans les annonces.');
        }

        if (in_array($slug, ['batiment', 'fournisseur'], true)) {
            return redirect()
                ->route('app.'.$slug.'.devis')
                ->with('status', 'Pour répondre à une demande, ouvrez la fiche depuis la liste des devis reçus.');
        }

        if ($slug === 'particulier' && (int) $request->query('owner_user_id', 0) <= 0) {
            return redirect()
                ->route('app.particulier.devis')
                ->with('status', 'Pour demander un devis, ouvrez une annonce dans le marketplace et utilisez « Demander un devis ».');
        }

        return $this->render($request, 'devis_create');
    }

    public function support(Request $request): View
    {
        return $this->render($request, 'support');
    }

    public function supportCreate(Request $request): View
    {
        return $this->render($request, 'support_create');
    }

    public function supportShow(Request $request, SupportTicket $ticket): View
    {
        abort_unless((int) $ticket->user_id === (int) $request->user()->id, 404);

        return $this->render($request, 'support_show', ['routeTicket' => $ticket]);
    }

    public function notificationsPage(Request $request): View
    {
        return $this->render($request, 'notifications');
    }

    public function besoinsManage(Request $request): View|RedirectResponse
    {
        if ($request->segment(2) === 'particulier') {
            return redirect()
                ->route('app.particulier.home')
                ->with('status', 'La gestion des besoins n’est pas disponible pour ce profil sur le web (aligné application mobile).');
        }

        return $this->render($request, 'besoins_manage');
    }

    public function besoinCreate(Request $request): View|RedirectResponse
    {
        if ($request->segment(2) === 'particulier') {
            return redirect()
                ->route('app.particulier.home')
                ->with('status', 'La publication de besoins n’est pas disponible pour le profil particulier (aligné application mobile).');
        }

        return $this->render($request, 'besoin_create');
    }

    public function servicesManage(Request $request): View
    {
        return $this->render($request, 'services_manage');
    }

    public function productsManage(Request $request): View
    {
        return $this->render($request, 'products_manage');
    }

    public function documents(Request $request): View|RedirectResponse
    {
        if ($request->segment(2) === 'particulier') {
            return redirect()
                ->route('app.particulier.settings')
                ->with('status', 'Les documents ne sont pas gérés pour le profil particulier (aligné application mobile).');
        }

        return $this->render($request, 'documents');
    }

    public function helpFournisseur(Request $request): View
    {
        return $this->render($request, 'help_fournisseur');
    }

    public function helpBatiment(Request $request): View
    {
        return $this->render($request, 'help_batiment');
    }

    public function helpParticulier(Request $request): View
    {
        return $this->render($request, 'help_particulier');
    }

    public function helpArtisan(Request $request): View
    {
        return $this->render($request, 'help_artisan');
    }

    public function supplierPublicPreview(Request $request): View
    {
        return $this->render($request, 'vue_publique');
    }

    public function batimentPublicPreview(Request $request): View
    {
        return $this->render($request, 'vue_publique_batiment');
    }

    public function candidatures(Request $request): View|RedirectResponse
    {
        $slug = (string) $request->segment(2);

        if (in_array($slug, ['particulier', 'fournisseur'], true)) {
            return redirect()
                ->route('app.'.$slug.'.home')
                ->with('status', 'Les candidatures ne sont pas disponibles pour ce profil (aligné application mobile).');
        }

        $default = match ($slug) {
            'artisan', 'fournisseur' => 'envoyees',
            'particulier' => 'recues',
            'batiment' => 'recues',
            default => 'recues',
        };
        $vue = (string) $request->query('vue', $default);
        if (! in_array($vue, ['recues', 'envoyees'], true)) {
            $vue = $default;
        }
        if ($slug === 'particulier' && $vue === 'envoyees') {
            $vue = 'recues';
        }
        if ($slug === 'artisan' && $vue === 'recues') {
            $vue = 'envoyees';
        }
        if ($slug === 'fournisseur' && $vue === 'recues') {
            $vue = 'envoyees';
        }

        return $this->render($request, 'candidatures', ['candidatureVue' => $vue]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function render(Request $request, string $page, array $extra = []): View
    {
        $slug = $request->segment(2);

        $titles = [
            'home' => 'Accueil',
            'dashboard_tab' => 'Tableau de bord',
            'messages' => 'Messages',
            'settings' => 'Paramètres',
            'profile' => 'Profil',
            'profile_password' => 'Mot de passe',
            'profile_location' => 'Localisation',
            'service_client' => 'Service client',
            'supplier_cart' => 'Panier',
            'marketplace' => 'Petites annonces',
            'devis' => match ($slug) {
                'fournisseur' => 'Mes devis',
                'particulier' => 'Commandes & devis',
                'artisan' => 'Missions & devis',
                'batiment' => 'Gestion des devis',
                default => 'Mes devis',
            },
            'fournisseur_orders' => 'Mes commandes',
            'devis_create' => match ($slug) {
                'fournisseur' => 'Nouvelle proposition',
                'particulier' => 'Demande de devis',
                default => 'Nouveau devis',
            },
            'devis_show' => $slug === 'fournisseur' ? 'Détail commande' : 'Détail devis',
            'support' => 'Support',
            'support_create' => 'Nouveau ticket',
            'support_show' => 'Ticket support',
            'notifications' => 'Notifications',
            'besoins_manage' => 'Mes besoins',
            'besoin_create' => 'Publier un besoin',
            'services_manage' => 'Mes services',
            'products_manage' => 'Mes produits',
            'product_form' => 'Produit',
            'documents' => 'Mes documents',
            'help_fournisseur' => 'Centre d’aide',
            'help_batiment' => 'Centre d’aide',
            'help_particulier' => 'Centre d’aide',
            'help_artisan' => 'Centre d’aide',
            'artisan_carte_visite' => 'Carte de visite',
            'vue_publique' => 'Vue publique',
            'vue_publique_batiment' => 'Vue publique',
            'besoin_form' => 'Besoin',
            'service_form' => 'Prestation',
            'candidatures' => 'Candidatures',
        ];

        /** @var MeApiBridge $bridge */
        $bridge = app(MeApiBridge::class);

        $routeDevis = $extra['routeDevis'] ?? null;
        $routeTicket = $extra['routeTicket'] ?? null;
        $routeBesoin = $extra['routeBesoin'] ?? null;
        $candidatureVue = $extra['candidatureVue'] ?? null;

        $candidatureBesoinFilter = 0;
        if ($page === 'candidatures') {
            $candidatureBesoinFilter = (int) $request->query('besoin_id', 0);
        }

        $viewData = [
            'profileSlug' => $slug,
            'page' => $page,
            'title' => $titles[$page] ?? ucfirst(str_replace('_', ' ', $page)),
            'intro' => $this->introFor($slug, $page),
            'unreadNotifications' => $bridge->unreadNotificationsCount($request),
            'apiError' => null,
            'dashboard' => null,
            'profileData' => null,
            'conversations' => [],
            'thread' => null,
            'peerId' => (int) $request->query('peer_id', 0),
            'notificationsPreview' => null,
            'notificationsFull' => null,
            'marketplaceData' => null,
            'devisList' => null,
            'devisDetail' => null,
            'supportList' => null,
            'ticketDetail' => null,
            'routeDevis' => $routeDevis,
            'routeTicket' => $routeTicket,
            'routeBesoin' => $routeBesoin,
            'candidatureVue' => $candidatureVue,
            'candidatureBesoinFilter' => $candidatureBesoinFilter,
            'besoinsList' => null,
            'servicesList' => null,
            'productsList' => null,
            'candidaturesList' => null,
            'supportFormOptions' => null,
            'metricsPeriodRoute' => null,
            'batimentAnalytics' => null,
            'supplierProducts' => null,
            'documentsList' => null,
            'helpFaqsList' => null,
            'categories' => null,
            'productFormMode' => null,
            'formProduct' => null,
            'serviceFormMode' => null,
            'formService' => null,
            'besoinFormMode' => null,
            'previewBesoins' => null,
            'previewServices' => null,
            'devisCanManage' => false,
            'devisCanRespondAsClient' => false,
            'documentUploadKinds' => [],
            'devisOwnerUserId' => 0,
            'devisRequestTitle' => '',
            'devisDirection' => DevisScopeService::DIRECTION_RECEIVED,
            'devisKind' => DevisScopeService::KIND_ALL,
            'devisBackUrl' => null,
            'devisShowQuote' => false,
        ];

        try {
            if ($page === 'support_create') {
                $viewData['supportFormOptions'] = $bridge->supportTicketFormOptions($request);
            }
            if ($page === 'dashboard_tab') {
                $viewData['dashboard'] = $bridge->dashboard($request, $slug);
                $viewData['metricsPeriodRoute'] = route('app.'.$slug.'.dashboard');
                if ($slug === 'batiment') {
                    $viewData['batimentAnalytics'] = $bridge->batimentDashboardAnalytics($request);
                }
            }
            if ($page === 'home') {
                $viewData['notificationsPreview'] = $bridge->notifications($request, 8);
                if (in_array($slug, ['particulier', 'artisan', 'batiment', 'fournisseur'], true)) {
                    $viewData['profileData'] = $bridge->profile($request);
                    $viewData['dashboard'] = $bridge->dashboard($request, $slug);
                    $viewData['metricsPeriodRoute'] = route('app.'.$slug.'.dashboard');
                }
                if ($slug === 'fournisseur') {
                    $viewData['supplierProducts'] = $bridge->myProducts($request);
                }
            }
            if ($page === 'notifications') {
                $perPage = min(50, max(5, (int) $request->query('per_page', 30)));
                $viewData['notificationsFull'] = $bridge->notifications($request, $perPage);
            }
            if (in_array($page, ['settings', 'profile', 'profile_password', 'profile_location'], true)) {
                $viewData['profileData'] = $bridge->profile($request);
            }
            if ($page === 'service_client' || $page === 'support') {
                $viewData['supportList'] = $bridge->supportTickets($request);
            }
            if ($page === 'devis_create') {
                $viewData['profileData'] = $bridge->profile($request);
                $viewData['devisOwnerUserId'] = (int) $request->query('owner_user_id', 0);
                $viewData['devisRequestTitle'] = trim((string) $request->query('title', ''));
            }
            if ($page === 'messages') {
                $viewData['conversations'] = $bridge->conversations($request);
                $viewData['thread'] = $bridge->messagesThread($request, $viewData['peerId']);
            }
            if ($page === 'marketplace') {
                $viewData['marketplaceData'] = $bridge->marketplace($request, $slug);
            }
            if (in_array($page, ['devis', 'fournisseur_orders'], true)) {
                $direction = (string) $request->query('direction', $this->defaultDevisDirection((string) $slug, $page));
                if ($slug === 'fournisseur' && $page === 'devis') {
                    $direction = DevisScopeService::DIRECTION_RECEIVED;
                } elseif (! in_array($direction, [DevisScopeService::DIRECTION_RECEIVED, DevisScopeService::DIRECTION_SENT], true)) {
                    $direction = $this->defaultDevisDirection((string) $slug, $page);
                }
                $kind = (string) $request->query('kind', DevisScopeService::KIND_ALL);
                if (! in_array($kind, [DevisScopeService::KIND_CATALOG, DevisScopeService::KIND_MARKETPLACE, DevisScopeService::KIND_ALL], true)) {
                    $kind = DevisScopeService::KIND_ALL;
                }
                $viewData['devisDirection'] = $direction;
                $viewData['devisKind'] = $kind;
                $viewData['devisList'] = $bridge->devisIndex(
                    $request,
                    $direction,
                    $kind,
                    $page === 'fournisseur_orders',
                    (string) $slug,
                );
            }
            if ($page === 'devis_show' && $routeDevis instanceof Devis) {
                $detail = $bridge->devisShow($request, $routeDevis);
                $userId = (int) ($request->user()?->id ?? 0);
                $scope = app(DevisScopeService::class);
                if (isset($detail['data']) && is_array($detail['data'])) {
                    $fromQuery = (string) $request->query('from', '');
                    $dir = $fromQuery === 'commandes'
                        ? DevisScopeService::DIRECTION_RECEIVED
                        : ((string) $request->query('direction', DevisScopeService::DIRECTION_RECEIVED));
                    $detail['data'] = $scope->enrichRow($detail['data'], $userId, $dir);
                }
                $viewData['devisDetail'] = $detail;
            }
            if ($page === 'support_show' && $routeTicket instanceof SupportTicket) {
                $viewData['ticketDetail'] = $bridge->supportTicket($request, $routeTicket);
            }
            if ($page === 'besoins_manage') {
                $viewData['besoinsList'] = $bridge->myBesoins($request);
            }
            if ($page === 'services_manage') {
                $viewData['servicesList'] = $bridge->myServices($request);
            }
            if ($page === 'products_manage') {
                $viewData['productsList'] = $bridge->myProducts($request);
            }
            if ($page === 'documents') {
                $viewData['documentsList'] = $bridge->userDocuments($request);
                $viewData['documentUploadKinds'] = $this->documentUploadKindsFor($request);
            }
            if (in_array($page, ['help_fournisseur', 'help_batiment', 'help_particulier', 'help_artisan'], true)) {
                $viewData['helpFaqsList'] = $bridge->helpFaqs();
            }
            if ($page === 'vue_publique') {
                $viewData['profileData'] = $bridge->profile($request);
                $viewData['productsList'] = $bridge->myProducts($request);
            }
            if ($page === 'vue_publique_batiment') {
                $viewData['profileData'] = $bridge->profile($request);
                $viewData['previewBesoins'] = $bridge->myBesoins($request);
                $viewData['previewServices'] = $bridge->myServices($request);
            }
            if ($page === 'candidatures' && $candidatureVue !== null) {
                $viewData['candidaturesList'] = $candidatureVue === 'recues'
                    ? $bridge->candidaturesReceived($request)
                    : $bridge->candidaturesAsApplicant($request);
            }
        } catch (\Throwable $e) {
            $viewData['apiError'] = config('app.debug')
                ? $e->getMessage()
                : 'Impossible de charger les données. Réessayez plus tard.';
        }

        if ($page === 'devis_show' && $routeDevis instanceof Devis) {
            $viewData['devisCanManage'] = $this->userCanManageDevis($request, $routeDevis);
            $viewData['devisCanRespondAsClient'] = $this->userCanRespondAsClientDevis($request, $routeDevis);
            $fromQuery = (string) $request->query('from', '');
            $detailRow = $viewData['devisDetail']['data'] ?? [];
            $quoteParam = $request->query('quote');
            $viewData['devisShowQuote'] = match (true) {
                in_array($quoteParam, ['0', 'false'], true) => false,
                in_array($quoteParam, ['1', 'true'], true) => true,
                default => is_array($detailRow)
                    && ! empty($detailRow['needs_supplier_quote'])
                    && ! empty($viewData['devisCanManage']),
            };
            $viewData['devisBackUrl'] = $fromQuery === 'commandes' && $slug === 'fournisseur'
                ? route('app.fournisseur.commandes')
                : route('app.'.$slug.'.devis', array_filter([
                    'direction' => $request->query('direction'),
                    'status' => $request->query('status'),
                    'mission' => $request->query('mission'),
                ]));
        }

        $mergeExtra = $extra;
        unset(
            $mergeExtra['routeDevis'],
            $mergeExtra['routeTicket'],
            $mergeExtra['routeBesoin'],
            $mergeExtra['candidatureVue'],
        );
        $viewData = array_merge($viewData, $mergeExtra);

        return view('app.shell.page', $viewData);
    }

    protected function userCanManageDevis(Request $request, Devis $devis): bool
    {
        $u = $request->user();
        if (! $u instanceof User) {
            return false;
        }

        return (int) $devis->user_id === (int) $u->id
            && in_array($u->profile_type, [
                User::PROFILE_ENTREPRENEUR_BATIMENT,
                User::PROFILE_ENTREPRISE_FOURNISSEUR,
                User::PROFILE_ARTISAN,
            ], true);
    }

    protected function defaultDevisDirection(string $slug, string $page): string
    {
        if ($page === 'fournisseur_orders') {
            return DevisScopeService::DIRECTION_RECEIVED;
        }

        return DevisScopeService::DIRECTION_RECEIVED;
    }

    protected function userCanRespondAsClientDevis(Request $request, Devis $devis): bool
    {
        $u = $request->user();
        if (! $u instanceof User) {
            return false;
        }

        if (! $devis->client_user_id || (int) $devis->client_user_id !== (int) $u->id) {
            return false;
        }

        if (in_array((string) $devis->status, ['valide', 'rejete'], true)) {
            return false;
        }

        if (! in_array((string) $devis->status, ['envoye', 'en_cours'], true)) {
            return false;
        }

        return in_array($u->profile_type, [
            User::PROFILE_PARTICULIER,
            User::PROFILE_ARTISAN,
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_ENTREPRISE_FOURNISSEUR,
        ], true);
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    protected function documentUploadKindsFor(Request $request): array
    {
        $u = $request->user();
        if (! $u instanceof User) {
            return [];
        }

        $kinds = match ($u->profile_type) {
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_ENTREPRISE_FOURNISSEUR => \App\Http\Controllers\Api\Me\UserDocumentController::companyComplianceKinds(),
            User::PROFILE_ARTISAN => \App\Http\Controllers\Api\Me\UserDocumentController::artisanComplianceKinds(),
            default => [],
        };

        return array_map(
            fn (string $code) => [
                'code' => $code,
                'label' => \App\Models\UserDocument::labelForKind($code),
            ],
            $kinds,
        );
    }

    protected function introFor(string $slug, string $page): string
    {
        return '';
    }
}
