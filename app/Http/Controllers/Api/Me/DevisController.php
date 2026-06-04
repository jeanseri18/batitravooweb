<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\Besoin;
use App\Models\Candidature;
use App\Models\Devis;
use App\Models\InAppNotification;
use App\Models\User;
use App\Services\BesoinDevisSyncService;
use App\Services\DevisStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DevisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        if (in_array($u->profile_type, [User::PROFILE_ENTREPRENEUR_BATIMENT, User::PROFILE_ENTREPRISE_FOURNISSEUR], true)) {
            $q = Devis::query()->where(function ($w) use ($u) {
                $w->where('user_id', $u->id)->orWhere('client_user_id', $u->id);
            });
        } elseif ($u->profile_type === User::PROFILE_PARTICULIER) {
            $q = Devis::query()->where('client_user_id', $u->id);
        } elseif ($u->profile_type === User::PROFILE_ARTISAN) {
            $q = Devis::query()->where(function ($w) use ($u) {
                $w->where('user_id', $u->id)->orWhere('client_user_id', $u->id);
            });
        } else {
            return response()->json(['message' => 'Profil non autorisé pour cette ressource.'], 403);
        }

        $st = $request->string('status')->trim()->toString();
        if ($st !== '') {
            $q->where('status', $st);
        }

        $items = $q->with(['user', 'clientUser'])->orderByDesc('id')->get()->map(fn (Devis $d) => $this->row($d));

        return response()->json([
            'data' => $items,
            'meta' => [
                'status_filter' => $this->statusFilterOptions(),
            ],
        ]);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusFilterOptions(): array
    {
        $opts = [
            ['value' => '', 'label' => 'Tous les statuts'],
        ];
        foreach (['non_traite', 'en_cours', 'envoye', 'valide', 'rejete'] as $code) {
            $opts[] = ['value' => $code, 'label' => $this->statusLabel($code)];
        }

        return $opts;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'non_traite' => 'Non traité',
            'en_cours' => 'En cours',
            'envoye' => 'Envoyé',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            default => $status,
        };
    }

    public function show(Request $request, Devis $devis): JsonResponse
    {
        $u = $request->user();
        if (! $this->canView($u, $devis)) {
            return response()->json(['message' => 'Non trouvé.'], 404);
        }

        $devis->load(['user', 'clientUser']);

        return response()->json(['data' => $this->row($devis, true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $u = $request->user();
        if ($u->profile_type === User::PROFILE_ARTISAN) {
            return $this->storeArtisanBesoinDevis($request, $u);
        }

        $data = $request->validate([
            'owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'order_reference' => ['nullable', 'string', 'max:64'],
            'place' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'line_items' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $owner = User::query()->findOrFail($data['owner_user_id']);
        if ((int) $owner->id === (int) $u->id) {
            return response()->json(['message' => 'Vous ne pouvez pas commander chez vous-même.'], 422);
        }
        if (! in_array($owner->profile_type, [
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_ENTREPRISE_FOURNISSEUR,
            User::PROFILE_ARTISAN,
        ], true) || $owner->isAdmin() || ! $owner->is_active) {
            return response()->json(['message' => 'Prestataire invalide.'], 422);
        }

        $toOtherPrestataire = (int) $owner->id !== (int) $u->id;

        // Client → autre prestataire (marketplace, panier fournisseur, demande de devis).
        if ($toOtherPrestataire) {
            if (! in_array($u->profile_type, [
                User::PROFILE_PARTICULIER,
                User::PROFILE_ENTREPRENEUR_BATIMENT,
                User::PROFILE_ENTREPRISE_FOURNISSEUR,
                User::PROFILE_ARTISAN,
            ], true)) {
                return response()->json(['message' => 'Profil non autorisé pour cette action.'], 403);
            }

            $devis = Devis::query()->create([
                'user_id' => $owner->id,
                'client_user_id' => $u->id,
                'title' => $data['title'],
                'client_name' => $data['client_name'],
                'order_reference' => $data['order_reference'] ?? null,
                'place' => $data['place'] ?? null,
                'contact' => $this->defaultClientContact($u, $data['contact'] ?? null),
                'line_items' => $data['line_items'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'non_traite',
            ]);
            $devis->load(['user', 'clientUser']);

            return response()->json(['data' => $this->row($devis, true)], 201);
        }

        // Bâtiment / fournisseur : brouillon interne (vous êtes le prestataire destinataire du devis).
        if (in_array($u->profile_type, [User::PROFILE_ENTREPRENEUR_BATIMENT, User::PROFILE_ENTREPRISE_FOURNISSEUR], true)) {
            $devis = Devis::query()->create([
                'user_id' => $u->id,
                'client_user_id' => null,
                'title' => $data['title'],
                'client_name' => $data['client_name'],
                'order_reference' => $data['order_reference'] ?? null,
                'place' => $data['place'] ?? null,
                'contact' => $data['contact'] ?? null,
                'line_items' => $data['line_items'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'non_traite',
            ]);
            $devis->load(['user', 'clientUser']);

            return response()->json(['data' => $this->row($devis, true)], 201);
        }

        return response()->json(['message' => 'Profil non autorisé pour cette action.'], 403);
    }

    /**
     * Devis d’opportunité : artisan → client (propriétaire du besoin) + candidature liée.
     */
    public function storeArtisanBesoinDevis(Request $request, User $u): JsonResponse
    {
        $data = $request->validate([
            'besoin_id' => ['required', 'integer', 'exists:besoins,id'],
            'title' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'order_reference' => ['nullable', 'string', 'max:64'],
            'place' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'line_items' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $besoin = Besoin::query()->with('user')->findOrFail($data['besoin_id']);
        if ($besoin->status !== 'open' || (int) $besoin->user_id === (int) $u->id) {
            return response()->json(['message' => 'Besoin indisponible.'], 422);
        }

        $exists = Candidature::query()
            ->where('besoin_id', $besoin->id)
            ->where('applicant_id', $u->id)
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'Candidature / devis déjà envoyé pour ce besoin.'], 422);
        }

        $client = User::query()->findOrFail($besoin->user_id);
        if ($client->isAdmin() || ! $client->is_active) {
            return response()->json(['message' => 'Client indisponible.'], 422);
        }

        $devis = Devis::query()->create([
            'user_id' => $u->id,
            'client_user_id' => $client->id,
            'title' => $data['title'],
            'client_name' => $data['client_name'],
            'order_reference' => $data['order_reference'] ?? 'BESOIN-'.$besoin->id,
            'place' => $data['place'] ?? $besoin->place,
            'contact' => $data['contact'] ?? $client->phone,
            'line_items' => $data['line_items'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'envoye',
        ]);

        Candidature::query()->create([
            'besoin_id' => $besoin->id,
            'applicant_id' => $u->id,
            'display_name' => $u->name,
            'profession' => null,
            'message' => 'Proposition de devis transmise (n° '.$devis->id.').',
            'status' => 'recu',
            'posted_at' => now(),
        ]);

        $besoin->increment('candidature_count');

        InAppNotification::query()->create([
            'user_id' => $besoin->user_id,
            'type' => InAppNotification::TYPE_CANDIDATURE,
            'title' => $u->name,
            'body' => 'a proposé un devis pour votre besoin',
            'data' => [
                'besoin_id' => $besoin->id,
                'applicant_id' => $u->id,
                'devis_id' => $devis->id,
            ],
        ]);

        $devis->load(['user', 'clientUser']);

        return response()->json(['data' => $this->row($devis, true)], 201);
    }

    public function update(Request $request, Devis $devis): JsonResponse
    {
        $u = $request->user();
        $asPrestataire = $this->canManage($u, $devis);
        $asClient = $this->canClientFinalizeStatus($u, $devis);

        if (! $asPrestataire && ! $asClient) {
            return response()->json(['message' => 'Non trouvé.'], 404);
        }

        $data = $request->validate([
            'status' => [
                'sometimes',
                'string',
                Rule::in(['non_traite', 'en_cours', 'envoye', 'valide', 'rejete']),
            ],
            'notes' => ['nullable', 'string', 'max:10000'],
            'line_items' => ['nullable', 'array'],
            'order_reference' => ['nullable', 'string', 'max:64'],
        ]);

        if ($asClient) {
            if (array_key_exists('line_items', $data) || array_key_exists('notes', $data) || array_key_exists('order_reference', $data)) {
                return response()->json(['message' => 'Seul le statut peut être mis à jour.'], 403);
            }
            if (! array_key_exists('status', $data) || ! in_array((string) $data['status'], ['valide', 'rejete'], true)) {
                return response()->json(['message' => 'Vous pouvez uniquement accepter ou refuser la proposition.'], 422);
            }
        }

        $previousStatus = (string) $devis->status;
        $stockService = app(DevisStockService::class);

        if (array_key_exists('line_items', $data)) {
            $incoming = $data['line_items'];
            $previous = $devis->line_items;
            if (is_array($previous) && is_array($incoming)) {
                $incoming = $stockService->mergeLineItemsPreservingOrderProducts($previous, $incoming);
            }
            $devis->line_items = $incoming;
        }
        if (array_key_exists('notes', $data)) {
            $devis->notes = $data['notes'];
        }
        if (array_key_exists('order_reference', $data)) {
            $devis->order_reference = $data['order_reference'];
        }
        if (array_key_exists('status', $data)) {
            $devis->status = $data['status'];
            if (in_array($data['status'], ['valide', 'rejete'], true) && $devis->processed_at === null) {
                $devis->processed_at = now()->toDateString();
            }
        }

        $willValidate = (string) $devis->status === 'valide' && $previousStatus !== 'valide';

        DB::transaction(function () use ($devis, $willValidate, $stockService, $previousStatus): void {
            $devis->save();
            if ($willValidate) {
                $stockService->deductForValidatedOrder($devis->fresh());
            }
            app(BesoinDevisSyncService::class)->syncAfterDevisStatusChange(
                $devis->fresh(),
                (string) $devis->status,
                $previousStatus,
            );
        });

        $devis->load(['user', 'clientUser']);

        return response()->json(['data' => $this->row($devis, true)]);
    }

    /**
     * Le client finalise la réponse du prestataire (accepter / refuser).
     */
    private function canClientFinalizeStatus(User $u, Devis $d): bool
    {
        if (! $d->client_user_id || (int) $d->client_user_id !== (int) $u->id) {
            return false;
        }

        if (in_array((string) $d->status, ['valide', 'rejete'], true)) {
            return false;
        }

        if (! in_array((string) $d->status, ['envoye', 'en_cours'], true)) {
            return false;
        }

        return in_array($u->profile_type, [
            User::PROFILE_PARTICULIER,
            User::PROFILE_ARTISAN,
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_ENTREPRISE_FOURNISSEUR,
        ], true);
    }

    private function canView(User $u, Devis $d): bool
    {
        if (in_array($u->profile_type, [User::PROFILE_ENTREPRENEUR_BATIMENT, User::PROFILE_ENTREPRISE_FOURNISSEUR, User::PROFILE_ARTISAN], true) && (int) $d->user_id === (int) $u->id) {
            return true;
        }
        if ($d->client_user_id && (int) $d->client_user_id === (int) $u->id) {
            return in_array($u->profile_type, [
                User::PROFILE_PARTICULIER,
                User::PROFILE_ENTREPRENEUR_BATIMENT,
                User::PROFILE_ENTREPRISE_FOURNISSEUR,
                User::PROFILE_ARTISAN,
            ], true);
        }

        return false;
    }

    private function canManage(User $u, Devis $d): bool
    {
        if ((int) $d->user_id !== (int) $u->id) {
            return false;
        }

        return in_array($u->profile_type, [User::PROFILE_ENTREPRENEUR_BATIMENT, User::PROFILE_ENTREPRISE_FOURNISSEUR, User::PROFILE_ARTISAN], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Devis $d, bool $full = false): array
    {
        $provider = $d->relationLoaded('user') ? $d->user : null;
        $client = $d->relationLoaded('clientUser') ? $d->clientUser : null;

        $r = [
            'id' => $d->id,
            'user_id' => $d->user_id,
            'client_user_id' => $d->client_user_id,
            'title' => $d->title,
            'client_name' => $d->client_name,
            'order_reference' => $d->order_reference,
            'place' => $d->place,
            'contact' => $d->contact,
            'status' => $d->status,
            'status_label' => $this->statusLabel((string) $d->status),
            'created_at' => $d->created_at?->toIso8601String(),
            'is_marketplace_request' => $this->isMarketplaceRequest($d),
            'prestataire_name' => $this->displayName($provider),
            'prestataire_profile_type' => $provider?->profile_type,
            'client_phone' => $this->clientPhoneForPrestataire($d, $client),
            'client_email' => $this->clientEmailForPrestataire($d, $client),
        ];
        if ($full) {
            $r['line_items'] = $d->line_items;
            $r['notes'] = $d->notes;
            $r['processed_at'] = $d->processed_at?->toIso8601String();
            $r['client_display_name'] = $this->displayName($client) ?? $d->client_name;
        }

        return $r;
    }

    private function isMarketplaceRequest(Devis $d): bool
    {
        if ($d->client_user_id === null) {
            return false;
        }
        $items = $d->line_items;
        if (is_array($items)) {
            $source = $items['source'] ?? null;
            if (is_string($source) && str_contains($source, 'marketplace')) {
                return true;
            }
        }
        $ref = (string) ($d->order_reference ?? '');
        if (str_starts_with($ref, 'PANIER-')) {
            return true;
        }
        $title = strtolower((string) $d->title);
        if (str_contains($title, 'demande marketplace') || str_contains($title, 'commande catalogue')) {
            return true;
        }

        return false;
    }

    private function displayName(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }
        $company = trim((string) ($user->company_name ?? ''));
        if ($company !== '') {
            return $company;
        }
        $name = trim((string) ($user->name ?? ''));

        return $name !== '' ? $name : null;
    }

    private function defaultClientContact(User $client, mixed $contact): ?string
    {
        $c = trim((string) ($contact ?? ''));
        if ($c !== '') {
            return $c;
        }
        $phone = trim((string) ($client->phone ?? ''));
        if ($phone !== '') {
            return $phone;
        }
        $email = trim((string) ($client->email ?? ''));

        return $email !== '' ? $email : null;
    }

    private function clientPhoneForPrestataire(Devis $d, ?User $client): ?string
    {
        $contact = trim((string) ($d->contact ?? ''));
        if ($contact !== '' && ! str_contains($contact, '@')) {
            return $contact;
        }
        if ($client === null) {
            return null;
        }
        $phone = trim((string) ($client->phone ?? ''));
        if ($phone !== '') {
            return $phone;
        }
        $mgr = trim((string) ($client->manager_contact ?? ''));
        if ($mgr !== '' && ! str_contains($mgr, '@')) {
            return $mgr;
        }

        return null;
    }

    private function clientEmailForPrestataire(Devis $d, ?User $client): ?string
    {
        $contact = trim((string) ($d->contact ?? ''));
        if ($contact !== '' && str_contains($contact, '@')) {
            return $contact;
        }
        if ($client === null) {
            return null;
        }
        $email = trim((string) ($client->email ?? ''));
        if ($email !== '') {
            return $email;
        }
        $contactEmail = trim((string) ($client->contact_email ?? ''));

        return $contactEmail !== '' ? $contactEmail : null;
    }
}
