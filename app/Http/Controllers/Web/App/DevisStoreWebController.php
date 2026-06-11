<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Api\Me\DevisController as ApiMeDevisController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Web\ManualDevisQuoteBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DevisStoreWebController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $slug = (string) $request->segment(2);

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if ($user->profile_type === User::PROFILE_ARTISAN) {
            return redirect()
                ->route('app.'.$slug.'.marketplace', ['tab' => 'besoins'])
                ->withErrors(['devis_store' => 'Pour un devis lié à un besoin, ouvrez la fiche besoin dans les annonces.']);
        }

        $validated = $request->validate([
            'owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'order_reference' => ['nullable', 'string', 'max:64'],
            'place' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'line_label' => ['nullable', 'array'],
            'line_label.*' => ['nullable', 'string', 'max:255'],
            'line_qty' => ['nullable', 'array'],
            'line_qty.*' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'line_unit_fcfa' => ['nullable', 'array'],
            'line_unit_fcfa.*' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'discount_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tva_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $email = trim((string) ($validated['client_email'] ?? ''));
        unset($validated['client_email']);

        $notes = trim((string) ($validated['notes'] ?? ''));
        if ($email !== '') {
            $notes = trim($notes !== '' ? $notes."\n\nE-mail client : ".$email : 'E-mail client : '.$email);
        }
        $validated['notes'] = $notes !== '' ? $notes : null;

        $request->merge($validated);

        if ($user->profile_type !== User::PROFILE_PARTICULIER) {
            $lineItems = app(ManualDevisQuoteBuilder::class)->buildFromRequest($request);
            if ($lineItems !== null) {
                $request->merge(['line_items' => $lineItems]);
            }
        }

        $response = app(ApiMeDevisController::class)->store($request);

        if ($response->getStatusCode() >= 400) {
            $payload = json_decode($response->getContent(), true);

            return redirect()->back()->withInput()->withErrors([
                'devis_store' => is_array($payload) && isset($payload['message'])
                    ? (string) $payload['message']
                    : 'Impossible de créer le devis.',
            ]);
        }

        $payload = json_decode($response->getContent(), true);
        $id = is_array($payload) ? (int) data_get($payload, 'data.id', 0) : 0;

        if ($id > 0) {
            return redirect()
                ->route('app.'.$slug.'.devis.show', ['devis' => $id])
                ->with('status', 'Demande de devis enregistrée.');
        }

        return redirect()->route('app.'.$slug.'.devis')->with('status', 'Demande de devis enregistrée.');
    }

}
