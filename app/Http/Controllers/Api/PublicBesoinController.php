<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Besoin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicBesoinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Besoin::query()->whereIn('status', ['open', 'in_progress'])->with('user');

        $search = $request->string('q')->trim()->toString();
        if ($search !== '') {
            $q->where(function ($b) use ($search) {
                $b->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('place', 'like', "%{$search}%");
            });
        }

        $owner = $request->string('owner')->toString();
        if ($owner === 'particulier') {
            $q->whereHas('user', static fn ($u) => $u->where(
                'profile_type',
                User::PROFILE_PARTICULIER,
            ));
        } elseif (in_array($owner, ['pro', 'entrepreneur_batiment'], true)) {
            $q->whereHas('user', static fn ($u) => $u->where(
                'profile_type',
                User::PROFILE_ENTREPRENEUR_BATIMENT,
            ));
        }

        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $paginated = $q->orderByDesc('id')->paginate($perPage);
        $paginated->getCollection()->transform(fn (Besoin $b) => $this->row($b));

        return response()->json($paginated);
    }

    public function show(Besoin $besoin): JsonResponse
    {
        if (! in_array($besoin->status, ['open', 'in_progress', 'closed'], true)) {
            return response()->json(['message' => 'Non trouvé.'], 404);
        }
        $besoin->load('user');

        return response()->json(['data' => $this->row($besoin, true)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Besoin $b, bool $detail = false): array
    {
        $imageUrl = $b->image_path
            ? storage_public_url($b->image_path)
            : null;

        $r = [
            'id' => $b->id,
            'title' => $b->title,
            'budget' => $b->budget,
            'start_label' => $b->start_label,
            'place' => $b->place,
            'description' => $b->description,
            'duration' => $b->duration,
            'short_date' => $b->short_date,
            'image_path' => $b->image_path,
            'image_url' => $imageUrl,
            'has_image' => $b->image_path !== null && $b->image_path !== '',
            'candidature_count' => (int) $b->candidature_count,
            'status' => $b->status,
            'user_id' => $b->user_id,
        ];
        if ($b->relationLoaded('user') && $b->user) {
            $u = $b->user;
            $displayName = trim((string) $u->name);
            if ($u->profile_type === User::PROFILE_ENTREPRENEUR_BATIMENT) {
                $company = trim((string) ($u->company_name ?? ''));
                if ($company !== '') {
                    $displayName = $company;
                }
            }
            if ($displayName === '') {
                $displayName = 'Utilisateur';
            }
            $r['owner'] = [
                'id' => $u->id,
                'name' => $displayName,
                'profile_type' => $u->profile_type,
                'phone' => $this->ownerPhone($u),
                'email' => $this->ownerEmail($u),
            ];
        }
        if ($detail) {
            $r['created_at'] = $b->created_at?->toIso8601String();
        }

        return $r;
    }

    private function ownerPhone(User $u): ?string
    {
        $phone = trim((string) ($u->phone ?? ''));
        if ($phone !== '') {
            return $phone;
        }
        $mgr = trim((string) ($u->manager_contact ?? ''));
        if ($mgr !== '' && ! str_contains($mgr, '@')) {
            return $mgr;
        }

        return null;
    }

    private function ownerEmail(User $u): ?string
    {
        $email = trim((string) ($u->email ?? ''));
        if ($email !== '') {
            return $email;
        }
        $contactEmail = trim((string) ($u->contact_email ?? ''));

        return $contactEmail !== '' ? $contactEmail : null;
    }
}
