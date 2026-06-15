<?php

namespace App\Services\Web;

use App\Http\Controllers\Api\HelpFaqController;
use App\Http\Controllers\Api\Me\ArtisanDashboardController;
use App\Http\Controllers\Api\Me\BatimentDashboardAnalyticsController;
use App\Http\Controllers\Api\Me\BatimentDashboardController;
use App\Http\Controllers\Api\Me\BesoinController as ApiMeBesoinController;
use App\Http\Controllers\Api\Me\CandidatureController as ApiMeCandidatureController;
use App\Http\Controllers\Api\Me\DevisController as ApiMeDevisController;
use App\Http\Controllers\Api\Me\FournisseurDashboardController;
use App\Http\Controllers\Api\Me\MessageController;
use App\Http\Controllers\Api\Me\NotificationController;
use App\Http\Controllers\Api\Me\ParticulierDashboardController;
use App\Http\Controllers\Api\Me\ProductController as ApiMeProductController;
use App\Http\Controllers\Api\Me\ProfileController;
use App\Http\Controllers\Api\Me\ServiceController as ApiMeServiceController;
use App\Http\Controllers\Api\Me\SupportTicketController as ApiMeSupportTicketController;
use App\Http\Controllers\Api\Me\UserDocumentController;
use App\Http\Controllers\Api\PublicBesoinController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicProductController;
use App\Http\Controllers\Api\PublicServiceController;
use App\Models\Devis;
use App\Models\SupportTicket;
use App\Services\DevisScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeApiBridge
{
    /**
     * @return array<string, mixed>
     */
    public function decode(JsonResponse $response): array
    {
        $json = $response->getContent();

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    public function assertOk(JsonResponse $response): void
    {
        $code = $response->getStatusCode();
        if ($code >= 400) {
            $payload = json_decode($response->getContent(), true);
            $msg = is_array($payload) && isset($payload['message'])
                ? (string) $payload['message']
                : 'Erreur API';
            abort($code, $msg);
        }
    }

    /**
     * Données tableau de bord (même règles que l’application mobile).
     *
     * @return array<string, mixed>
     */
    public function dashboard(Request $request, string $slug): array
    {
        $controller = match ($slug) {
            'particulier' => app(ParticulierDashboardController::class),
            'artisan' => app(ArtisanDashboardController::class),
            'batiment' => app(BatimentDashboardController::class),
            'fournisseur' => app(FournisseurDashboardController::class),
            default => throw new \InvalidArgumentException('Profil inconnu: '.$slug),
        };

        $response = $controller($request);
        $this->assertOk($response);

        return $this->decode($response);
    }

    /**
     * Séries analytiques onglet Dashboard BTP (GET /api/me/dashboard/batiment/analytics).
     *
     * @return array<string, mixed>
     */
    public function batimentDashboardAnalytics(Request $request): array
    {
        $response = app(BatimentDashboardAnalyticsController::class)($request);
        $this->assertOk($response);

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function profile(Request $request): array
    {
        $response = app(ProfileController::class)->show($request);
        $this->assertOk($response);

        return $this->decode($response);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function conversations(Request $request): array
    {
        $response = app(MessageController::class)->conversationPartners($request);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * @return array{data: list<array<string, mixed>>}|null
     */
    public function messagesThread(Request $request, int $peerId): ?array
    {
        if ($peerId <= 0) {
            return null;
        }
        $sub = $request->duplicate(['peer_id' => $peerId]);
        $response = app(MessageController::class)->index($sub);
        if ($response->getStatusCode() >= 400) {
            return null;
        }

        return $this->decode($response);
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function notifications(Request $request, int $perPage = 15): array
    {
        $sub = $request->duplicate(['per_page' => $perPage]);
        $response = app(NotificationController::class)->index($sub);
        $this->assertOk($response);

        return $this->decode($response);
    }

    public function unreadNotificationsCount(Request $request): int
    {
        try {
            $payload = $this->notifications($request, 1);

            return (int) ($payload['meta']['unread_count'] ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Options du formulaire ticket support (GET /api/me/tickets/form-options).
     *
     * @return array<string, mixed>
     */
    public function supportTicketFormOptions(Request $request): array
    {
        $response = app(ApiMeSupportTicketController::class)->formOptions($request);
        $this->assertOk($response);

        return $this->decode($response);
    }

    /**
     * Agrégation marketplace : catégories + produits / services / besoins publics (API `/api/...`).
     *
     * @return array<string, mixed>
     */
    public function marketplace(Request $request, string $profileSlug): array
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 12)));

        $catParams = [];
        if ($request->filled('cat_scope')) {
            $scope = (string) $request->query('cat_scope');
            if (in_array($scope, ['product', 'service', 'both'], true)) {
                $catParams['applies_to'] = $scope;
            }
        }
        $categoriesReq = Request::create('/', 'GET', $catParams);
        $categoriesResponse = app(PublicCategoryController::class)($categoriesReq);
        $this->assertOk($categoriesResponse);
        $categoriesPayload = $this->decode($categoriesResponse);

        $productQuery = array_filter([
            'per_page' => $perPage,
            'q' => $request->filled('q') ? (string) $request->query('q') : null,
            'category_id' => $request->filled('category_id') ? (int) $request->query('category_id') : null,
            'user_id' => ($request->filled('user_id') && (int) $request->query('user_id') > 0)
                ? (int) $request->query('user_id')
                : null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== 0);

        $serviceKind = $request->filled('service_kind') ? (string) $request->query('service_kind') : null;
        if ($profileSlug === 'batiment' && $serviceKind === 'entrepreneur') {
            $serviceKind = 'artisan';
        }
        $serviceQuery = array_merge($productQuery, array_filter([
            'service_kind' => $serviceKind,
        ], fn ($v) => $v !== null && $v !== ''));

        $besoinQuery = array_filter([
            'per_page' => $perPage,
            'q' => $request->filled('q') ? (string) $request->query('q') : null,
            'owner' => $request->filled('owner') ? (string) $request->query('owner') : null,
        ], fn ($v) => $v !== null && $v !== '');

        $supplierCatalog = isset($productQuery['user_id']) && (int) $productQuery['user_id'] > 0;
        if ($supplierCatalog) {
            $productsPayload = $this->fetchAllPublicProducts($request, $productQuery);
        } else {
            $productsReq = Request::create('/', 'GET', $productQuery);
            $productsReq->setUserResolver(fn () => $request->user());
            $productsResponse = app(PublicProductController::class)->index($productsReq);
            $this->assertOk($productsResponse);
            $productsPayload = $this->decode($productsResponse);
        }

        $servicesReq = Request::create('/', 'GET', $serviceQuery);
        $servicesResponse = app(PublicServiceController::class)->index($servicesReq);
        $this->assertOk($servicesResponse);
        $servicesPayload = $this->decode($servicesResponse);
        if ($profileSlug === 'batiment' && is_array($servicesPayload['data'] ?? null)) {
            $servicesPayload['data'] = array_values(array_filter(
                $servicesPayload['data'],
                static fn (array $row): bool => ($row['service_kind'] ?? '') !== 'entrepreneur',
            ));
        }

        $besoinsReq = Request::create('/', 'GET', $besoinQuery);
        $besoinsResponse = app(PublicBesoinController::class)->index($besoinsReq);
        $this->assertOk($besoinsResponse);
        $besoinsPayload = $this->decode($besoinsResponse);

        return [
            'profile_slug' => $profileSlug,
            'categories' => $categoriesPayload['data'] ?? [],
            'products' => $productsPayload,
            'services' => $servicesPayload,
            'besoins' => $besoinsPayload,
            'filters' => [
                'q' => $request->query('q', ''),
                'category_id' => $request->query('category_id', ''),
                'service_kind' => $request->query('service_kind', ''),
                'owner' => $request->query('owner', ''),
                'user_id' => $request->query('user_id', ''),
                'cat_scope' => $request->query('cat_scope', ''),
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * Liste devis filtrée côté web (parité mobile devis_scope.dart).
     *
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function devisIndex(
        Request $request,
        ?string $direction = null,
        ?string $kind = null,
        bool $supplierOrdersOnly = false,
        ?string $profileSlug = null,
    ): array {
        $response = app(ApiMeDevisController::class)->index($request);
        $this->assertOk($response);

        $payload = $this->decode($response);
        $rows = $payload['data'] ?? [];
        if (! is_array($rows)) {
            $rows = [];
        }

        $userId = (int) ($request->user()?->id ?? 0);
        $slug = $profileSlug ?? (string) $request->segment(2);
        $scope = app(DevisScopeService::class);

        $direction = in_array($direction, [DevisScopeService::DIRECTION_RECEIVED, DevisScopeService::DIRECTION_SENT], true)
            ? $direction
            : null;
        $kind = in_array($kind, [DevisScopeService::KIND_CATALOG, DevisScopeService::KIND_MARKETPLACE, DevisScopeService::KIND_ALL], true)
            ? $kind
            : DevisScopeService::KIND_ALL;

        $statusFilter = trim((string) $request->query('status', ''));
        $missionFilter = trim((string) $request->query('mission', 'all'));

        $kindFilter = $kind === DevisScopeService::KIND_ALL ? null : $kind;
        $statusCounts = $scope->countByStatusChip(
            $rows,
            $userId,
            $slug,
            $direction,
            $kindFilter,
            $supplierOrdersOnly,
            $missionFilter !== '' ? $missionFilter : null,
        );

        $filtered = $scope->filterForProfile(
            $rows,
            $userId,
            $slug,
            $direction,
            $kindFilter,
            $supplierOrdersOnly,
            $statusFilter !== '' ? $statusFilter : null,
            $missionFilter !== '' ? $missionFilter : null,
        );

        $dirForEnrich = $direction ?? DevisScopeService::DIRECTION_RECEIVED;
        $payload['data'] = array_map(
            fn (array $row) => $scope->enrichRow($row, $userId, $dirForEnrich),
            $filtered,
        );
        $payload['meta'] = array_merge($payload['meta'] ?? [], [
            'direction' => $direction,
            'kind' => $kind,
            'supplier_orders_only' => $supplierOrdersOnly,
            'total_filtered' => count($payload['data']),
            'status_counts' => $statusCounts,
        ]);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function devisShow(Request $request, Devis $devis): array
    {
        $response = app(ApiMeDevisController::class)->show($request, $devis);
        $this->assertOk($response);

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function supportTickets(Request $request): array
    {
        $response = app(ApiMeSupportTicketController::class)->index($request);
        $this->assertOk($response);

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function supportTicket(Request $request, SupportTicket $ticket): array
    {
        $response = app(ApiMeSupportTicketController::class)->show($request, $ticket);
        $this->assertOk($response);

        return $this->decode($response);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function myBesoins(Request $request): array
    {
        $response = app(ApiMeBesoinController::class)->index($request);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function myServices(Request $request): array
    {
        $response = app(ApiMeServiceController::class)->index($request);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function myProducts(Request $request): array
    {
        $response = app(ApiMeProductController::class)->index($request);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function fetchAllPublicProducts(Request $request, array $query): array
    {
        $all = [];
        $page = 1;
        $lastPage = 1;
        $perPage = (int) ($query['per_page'] ?? 50);

        do {
            $sub = Request::create('/', 'GET', array_merge($query, [
                'page' => $page,
                'per_page' => $perPage,
            ]));
            $sub->setUserResolver(fn () => $request->user());
            $response = app(PublicProductController::class)->index($sub);
            $this->assertOk($response);
            $payload = $this->decode($response);
            $chunk = $payload['data'] ?? [];
            if (is_array($chunk)) {
                $all = array_merge($all, $chunk);
            }
            $lastPage = (int) ($payload['last_page'] ?? 1);
            $page++;
        } while ($page <= $lastPage);

        return [
            'data' => $all,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => count($all) > 0 ? count($all) : $perPage,
            'total' => count($all),
        ];
    }

    /**
     * Catalogue public du fournisseur connecté (même liste que les clients).
     *
     * @return list<array<string, mixed>>
     */
    public function publicCatalogProducts(Request $request): array
    {
        $userId = (int) $request->user()->id;
        if ($userId <= 0) {
            return [];
        }

        $payload = $this->fetchAllPublicProducts($request, [
            'user_id' => $userId,
            'per_page' => 50,
        ]);

        return $payload['data'] ?? [];
    }

    /**
     * Catégories pour formulaires produit ([GET /api/categories?applies_to=product]).
     *
     * @return list<array<string, mixed>>
     */
    public function categoriesForProducts(Request $request): array
    {
        $sub = Request::create('/', 'GET', ['applies_to' => 'product']);
        $response = app(PublicCategoryController::class)($sub);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * Catégories pour formulaires service ([GET /api/categories?applies_to=service]).
     *
     * @return list<array<string, mixed>>
     */
    public function categoriesForServices(Request $request): array
    {
        $sub = Request::create('/', 'GET', ['applies_to' => 'service']);
        $response = app(PublicCategoryController::class)($sub);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * Documents utilisateur ([GET /api/me/documents]).
     *
     * @return list<array<string, mixed>>
     */
    public function userDocuments(Request $request): array
    {
        $response = app(UserDocumentController::class)->index($request);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * FAQ centre d’aide ([GET /api/help/faqs]).
     *
     * @return list<array<string, mixed>>
     */
    public function helpFaqs(): array
    {
        $response = app(HelpFaqController::class)->index();
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function candidaturesReceived(Request $request): array
    {
        $response = app(ApiMeCandidatureController::class)->indexReceived($request);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function candidaturesAsApplicant(Request $request): array
    {
        $response = app(ApiMeCandidatureController::class)->indexAsApplicant($request);
        $this->assertOk($response);
        $data = $this->decode($response);

        return $data['data'] ?? [];
    }
}
