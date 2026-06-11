<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\Besoin;
use App\Models\Candidature;
use App\Models\Devis;
use App\Models\InAppNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CandidatureController extends Controller
{
    /**
     * Candidatures reçues sur les besoins publiés par l’utilisateur (entrepreneur / particulier).
     */
    public function indexReceived(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless(in_array($u->profile_type, [
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_PARTICULIER,
        ], true), 403);

        $q = Candidature::query()
            ->whereHas('besoin', static fn ($b) => $b->where('user_id', $u->id));

        $besoinId = (int) $request->query('besoin_id', 0);
        if ($besoinId > 0) {
            $q->where('besoin_id', $besoinId);
        }

        $items = $q->with(['besoin', 'applicant'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Candidature $c) => $this->row($c));

        return response()->json(['data' => $items]);
    }

    public function indexAsApplicant(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless(in_array($u->profile_type, [
            User::PROFILE_ARTISAN,
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_ENTREPRISE_FOURNISSEUR,
        ], true), 403);

        $items = Candidature::query()
            ->where('applicant_id', $u->id)
            ->with(['besoin', 'applicant'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Candidature $c) => $this->row($c));

        return response()->json(['data' => $items]);
    }

    public function indexForBesoin(Request $request, Besoin $besoin): JsonResponse
    {
        $u = $request->user();
        abort_unless($besoin->user_id === $u->id, 404);
        abort_unless(in_array($u->profile_type, [
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_PARTICULIER,
        ], true), 403);

        $items = Candidature::query()
            ->where('besoin_id', $besoin->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (Candidature $c) => $this->row($c));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless(in_array($u->profile_type, [
            User::PROFILE_ARTISAN,
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_ENTREPRISE_FOURNISSEUR,
        ], true), 403);

        $data = $request->validate([
            'besoin_id' => ['required', 'integer', 'exists:besoins,id'],
            'message' => ['nullable', 'string', 'max:10000'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
        ]);

        $besoin = Besoin::query()->findOrFail($data['besoin_id']);
        if ($besoin->status !== 'open' || (int) $besoin->user_id === (int) $u->id) {
            return response()->json(['message' => 'Besoin indisponible.'], 422);
        }

        $exists = Candidature::query()
            ->where('besoin_id', $besoin->id)
            ->where('applicant_id', $u->id)
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'Candidature déjà envoyée.'], 422);
        }

        $c = Candidature::query()->create([
            'besoin_id' => $besoin->id,
            'applicant_id' => $u->id,
            'display_name' => $data['display_name'] ?? $u->name,
            'profession' => $data['profession'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'recu',
            'posted_at' => now(),
        ]);

        $besoin->increment('candidature_count');

        InAppNotification::query()->create([
            'user_id' => $besoin->user_id,
            'type' => InAppNotification::TYPE_CANDIDATURE,
            'title' => $u->name,
            'body' => 'a postulé à votre besoin',
            'data' => [
                'candidature_id' => $c->id,
                'besoin_id' => $besoin->id,
                'applicant_id' => $u->id,
            ],
        ]);

        return response()->json(['data' => $this->row($c)], 201);
    }

    public function update(Request $request, Candidature $candidature): JsonResponse
    {
        $u = $request->user();
        $besoin = $candidature->besoin;
        abort_unless($besoin && (int) $besoin->user_id === (int) $u->id, 404);
        abort_unless(in_array($u->profile_type, [
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_PARTICULIER,
        ], true), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['recu', 'accepte', 'rejete'])],
        ]);

        $candidature->status = $data['status'];
        $candidature->save();

        $candidature->load(['besoin', 'applicant']);

        return response()->json(['data' => $this->row($candidature)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Candidature $c): array
    {
        $applicant = $c->relationLoaded('applicant') ? $c->applicant : null;
        $devisId = $this->resolveDevisId($c);
        $devisPayload = null;
        if ($devisId !== null) {
            $devis = Devis::query()->find($devisId);
            if ($devis !== null) {
                $devisPayload = [
                    'id' => $devis->id,
                    'title' => $devis->title,
                    'status' => $devis->status,
                    'status_label' => $this->devisStatusLabel($devis->status),
                    'order_reference' => $devis->order_reference,
                ];
            }
        }

        $town = trim(implode(', ', array_values(array_filter([
            trim((string) ($applicant?->commune ?? '')),
            trim((string) ($applicant?->city ?? '')),
        ], fn (string $v) => $v !== ''))));
        if ($town === '') {
            $town = trim((string) ($applicant?->company_address ?? ''));
        }

        $r = [
            'id' => $c->id,
            'besoin_id' => $c->besoin_id,
            'applicant_id' => $c->applicant_id,
            'display_name' => $c->display_name,
            'profession' => $c->profession,
            'status' => $c->status,
            'message' => $c->message,
            'posted_at' => $c->posted_at?->toIso8601String(),
            'applicant_phone' => $applicant?->phone,
            'applicant_email' => $applicant?->email,
            'applicant_town' => $town !== '' ? $town : null,
            'devis_id' => $devisId,
            'devis' => $devisPayload,
        ];
        if ($c->relationLoaded('besoin') && $c->besoin) {
            $r['besoin'] = [
                'id' => $c->besoin->id,
                'title' => $c->besoin->title,
                'status' => $c->besoin->status,
            ];
        }

        return $r;
    }

    private function resolveDevisId(Candidature $c): ?int
    {
        $message = trim((string) ($c->message ?? ''));
        if ($message !== '' && preg_match('/n°\s*(\d+)/u', $message, $m) === 1) {
            $fromMessage = (int) $m[1];
            if ($fromMessage > 0) {
                $exists = Devis::query()
                    ->where('id', $fromMessage)
                    ->where('user_id', $c->applicant_id)
                    ->exists();
                if ($exists) {
                    return $fromMessage;
                }
            }
        }

        $besoin = $c->relationLoaded('besoin') ? $c->besoin : Besoin::query()->find($c->besoin_id);
        if ($besoin === null) {
            return null;
        }

        $devis = Devis::query()
            ->where('user_id', $c->applicant_id)
            ->where('client_user_id', $besoin->user_id)
            ->where('order_reference', 'BESOIN-'.$c->besoin_id)
            ->orderByDesc('id')
            ->first();

        return $devis?->id;
    }

    private function devisStatusLabel(string $status): string
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
}
