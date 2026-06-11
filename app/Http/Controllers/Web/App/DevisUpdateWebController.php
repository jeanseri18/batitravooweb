<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Api\Me\DevisController as ApiMeDevisController;
use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Services\DevisScopeService;
use App\Services\Web\ManualDevisQuoteBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DevisUpdateWebController extends Controller
{
    public function __invoke(Request $request, Devis $devis): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', Rule::in(['validate', 'reject', 'quote_send', 'quote_draft'])],
            'status' => ['nullable', 'string', Rule::in(['non_traite', 'en_cours', 'envoye', 'valide', 'rejete'])],
            'notes' => ['nullable', 'string', 'max:10000'],
            'order_reference' => ['nullable', 'string', 'max:64'],
            'line_label' => ['nullable', 'array'],
            'line_label.*' => ['nullable', 'string', 'max:255'],
            'line_qty' => ['nullable', 'array'],
            'line_qty.*' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'line_unit_fcfa' => ['nullable', 'array'],
            'line_unit_fcfa.*' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'discount_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tva_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $action = (string) ($validated['action'] ?? '');
        $data = $validated;
        unset($data['action'], $data['line_label'], $data['line_qty'], $data['line_unit_fcfa'], $data['discount_pct'], $data['tva_pct']);

        if ($action === 'validate') {
            $data['status'] = 'valide';
        } elseif ($action === 'reject') {
            $data['status'] = 'rejete';
        } elseif (in_array($action, ['quote_send', 'quote_draft'], true)) {
            $lineItems = app(ManualDevisQuoteBuilder::class)->buildFromRequest($request);
            if ($lineItems === null) {
                return redirect()->back()->withInput()->withErrors([
                    'devis_update' => 'Ajoutez au moins une ligne avec un libellé et un montant.',
                ]);
            }
            $data['line_items'] = $lineItems;
            $data['status'] = $action === 'quote_send' ? 'envoye' : 'en_cours';
        }

        if (($data['status'] ?? '') === '') {
            unset($data['status']);
        }

        if (($data['status'] ?? '') === 'valide') {
            $scope = app(DevisScopeService::class);
            $show = app(ApiMeDevisController::class)->show($request, $devis);
            $payload = json_decode($show->getContent(), true);
            $row = is_array($payload) ? ($payload['data'] ?? []) : [];
            if (is_array($row) && $scope->orderNeedsSupplierQuote($row)) {
                return redirect()->back()->withErrors([
                    'devis_update' => 'Établissez d’abord le devis (prix sur les lignes) avant de valider la commande.',
                ]);
            }
        }

        $request->merge($data);

        $response = app(ApiMeDevisController::class)->update($request, $devis);

        if ($response->getStatusCode() >= 400) {
            $payload = json_decode($response->getContent(), true);

            return redirect()->back()->withInput()->withErrors([
                'devis_update' => is_array($payload) && isset($payload['message'])
                    ? (string) $payload['message']
                    : 'Impossible de mettre à jour le devis.',
            ]);
        }

        return redirect()->back()->with('status', 'Devis mis à jour.');
    }
}
