<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\PublicMarketplaceProviderController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnuaireController extends Controller
{
    public function __invoke(Request $request)
    {
        $kind = $request->query('kind', '');
        if (! in_array($kind, ['', 'fournisseur', 'artisan', 'btp'], true)) {
            $kind = '';
        }

        $q = trim((string) $request->query('q', ''));

        $apiRequest = Request::create('/api/marketplace/providers', 'GET', array_filter([
            'kind' => $kind,
            'q' => $q !== '' ? $q : null,
            'per_page' => 100,
        ], fn ($v) => $v !== null && $v !== ''));

        $response = app(PublicMarketplaceProviderController::class)->index($apiRequest);
        $payload = $response->getData(true);

        return view('vitrine.annuaire', [
            'providers' => $payload['data'] ?? [],
            'total' => (int) ($payload['total'] ?? 0),
            'kind' => $kind,
            'q' => $q,
        ]);
    }
}
