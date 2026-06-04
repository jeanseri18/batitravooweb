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

        $search = $request->string('q')->trim()->toString();
        if ($search !== '') {
            $q->where(function ($b) use ($search) {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('company_address', 'like', "%{$search}%")
                    ->orWhere('town', 'like', "%{$search}%")
                    ->orWhere('activity', 'like', "%{$search}%");
            });
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 50)));
        $paginated = $q->orderByRaw('COALESCE(NULLIF(company_name, ""), name) ASC')
            ->paginate($perPage);

        $paginated->getCollection()->transform(fn (User $u) => $this->row($u));

        return response()->json($paginated);
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
    private function row(User $u): array
    {
        $displayName = $this->displayName($u);
        $location = trim((string) ($u->company_address ?? ''));
        if ($location === '') {
            $location = trim((string) ($u->town ?? ''));
        }

        return [
            'user_id' => $u->id,
            'display_name' => $displayName,
            'profile_type' => $u->profile_type,
            'company_name' => $u->company_name,
            'location' => $location !== '' ? $location : null,
            'activity' => $u->activity,
            'avatar_url' => $u->avatar_path
                ? storage_public_url($u->avatar_path)
                : null,
        ];
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
