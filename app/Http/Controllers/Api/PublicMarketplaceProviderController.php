<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesOptionalApiUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Prestataires marketplace (fournisseur, artisan, BTP) — affichés même sans catalogue approuvé.
 */
class PublicMarketplaceProviderController extends Controller
{
    use ResolvesOptionalApiUser;

    public function index(Request $request): JsonResponse
    {
        $kind = $request->string('kind')->trim()->toString();
        $profileTypes = $this->profileTypesForKind($kind);

        $q = User::query()
            ->where('role', User::ROLE_USER)
            ->where('is_active', true)
            ->whereNotNull('profile_completed_at')
            ->where('profile_validation_status', User::VALIDATION_APPROVED)
            ->whereIn('profile_type', $profileTypes);

        $this->excludeSelfFromMarketplace($q, $request);
        $this->excludeEntrepreneurProfilesForBatimentViewer($q, $request);

        $search = $request->string('q')->trim()->toString();
        if ($search !== '') {
            // Champs visibles sur les cartes marketplace (nom, activité, localisation).
            $q->where(function ($b) use ($search) {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('activity_type', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('commune', 'like', "%{$search}%");
            });
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 50)));
        $paginated = $q->orderByRaw('COALESCE(NULLIF(company_name, ""), name) ASC')
            ->paginate($perPage);

        $paginated->getCollection()->transform(fn (User $u) => $this->row($u));

        return response()->json($paginated);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        abort_unless(
            $user->role === User::ROLE_USER
                && $user->is_active
                && $user->profile_completed_at !== null
                && $user->profile_validation_status === User::VALIDATION_APPROVED
                && in_array($user->profile_type, [
                    User::PROFILE_ENTREPRISE_FOURNISSEUR,
                    User::PROFILE_ARTISAN,
                    User::PROFILE_ENTREPRENEUR_BATIMENT,
                ], true),
            404,
        );

        if ($request->user()?->id === $user->id) {
            abort(404);
        }

        if ($this->isBatimentViewer($request)
            && $user->profile_type === User::PROFILE_ENTREPRENEUR_BATIMENT) {
            abort(404);
        }

        return response()->json(['data' => $this->row($user, true)]);
    }

    /**
     * @return list<string>
     */
    private function profileTypesForKind(string $kind): array
    {
        return match ($kind) {
            'fournisseur', 'fournisseurs' => [User::PROFILE_ENTREPRISE_FOURNISSEUR],
            'artisan', 'artisans' => [User::PROFILE_ARTISAN],
            'entrepreneur', 'btp', 'entrepreneur_batiment', 'entreprise' => [
                User::PROFILE_ENTREPRENEUR_BATIMENT,
            ],
            default => [
                User::PROFILE_ENTREPRISE_FOURNISSEUR,
                User::PROFILE_ARTISAN,
                User::PROFILE_ENTREPRENEUR_BATIMENT,
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function row(User $u, bool $detail = false): array
    {
        $displayName = $this->displayName($u);
        $description = trim((string) ($u->company_description ?? ''));
        if ($description === '') {
            $description = trim((string) ($u->bio ?? ''));
        }

        $row = [
            'user_id' => $u->id,
            'display_name' => $displayName,
            'profile_type' => $u->profile_type,
            'company_name' => $u->company_name,
            'location' => $this->resolveLocation($u),
            'activity' => $u->activity_type,
            'expertise' => $u->activity_type,
            'description' => $description !== '' ? $description : null,
            'avatar_url' => $u->avatar_path
                ? storage_public_url($u->avatar_path)
                : null,
        ];

        if ($detail) {
            $row['company_address'] = $u->company_address;
        }

        return $row;
    }

    private function resolveLocation(User $u): ?string
    {
        $parts = array_values(array_filter([
            trim((string) ($u->commune ?? '')),
            trim((string) ($u->city ?? '')),
        ], fn (string $v) => $v !== ''));

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        $address = trim((string) ($u->company_address ?? ''));

        return $address !== '' ? $address : null;
    }

    private function displayName(User $u): string
    {
        $company = trim((string) ($u->company_name ?? ''));
        if ($company !== '') {
            return $company;
        }
        $name = trim((string) ($u->name ?? ''));

        return $name !== '' ? $name : 'Prestataire';
    }
}
