<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\FormatsServiceApi;
use App\Http\Controllers\Api\Concerns\ResolvesOptionalApiUser;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicServiceController extends Controller
{
    use FormatsServiceApi;
    use ResolvesOptionalApiUser;

    public function index(Request $request): JsonResponse
    {
        $q = Service::query()
            ->where('is_visible', true)
            ->whereHas('user', function ($b) {
                $b->whereIn('profile_type', [
                    User::PROFILE_ARTISAN,
                    User::PROFILE_ENTREPRENEUR_BATIMENT,
                ])->where('is_active', true);
            })
            ->with(['category', 'user']);

        if (! $request->filled('user_id')) {
            $this->excludeSelfFromMarketplace($q, $request, 'user_id');
        }

        if ($this->isBatimentViewer($request)) {
            $q->whereHas('user', fn ($b) => $b->where(
                'profile_type',
                '!=',
                User::PROFILE_ENTREPRENEUR_BATIMENT
            ));
        }

        $kind = $request->string('service_kind')->trim()->toString();
        if ($kind !== '') {
            if ($kind === 'entrepreneur') {
                $q->whereHas('user', fn ($b) => $b->where(
                    'profile_type',
                    User::PROFILE_ENTREPRENEUR_BATIMENT
                ));
            } elseif ($kind === 'artisan') {
                $q->whereHas('user', fn ($b) => $b->where(
                    'profile_type',
                    User::PROFILE_ARTISAN
                ));
            }
        }
        if ($request->filled('user_id')) {
            $q->where('user_id', (int) $request->input('user_id'));
        }
        $search = $request->string('q')->trim()->toString();
        if ($search !== '') {
            $q->where(function ($b) use ($search) {
                $b->where('title', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('company_name', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('activity_type', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('category_id')) {
            $q->where('category_id', (int) $request->input('category_id'));
        }

        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $paginated = $q->orderByDesc('id')->paginate($perPage);
        $paginated->getCollection()->transform(function (Service $s) {
            return $this->toRow($s);
        });

        return response()->json($paginated);
    }

    public function show(Service $service): JsonResponse
    {
        if (! $service->is_visible) {
            return response()->json(['message' => 'Non trouvé.'], 404);
        }

        $service->load(['category', 'user']);

        if ($this->isBatimentViewer($request)
            && $service->user?->profile_type === User::PROFILE_ENTREPRENEUR_BATIMENT) {
            return response()->json(['message' => 'Non trouvé.'], 404);
        }

        return response()->json(['data' => $this->toRow($service, true)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(Service $s, bool $detail = false): array
    {
        $imageUrl = $this->resolveServiceImageUrl($s);

        $row = [
            'id' => $s->id,
            'title' => $s->title,
            'slug' => $s->slug,
            'description' => $s->description,
            'location' => $s->location,
            'image_path' => $s->image_path,
            'image_url' => $imageUrl,
            'has_image' => $imageUrl !== null,
            'service_kind' => $this->effectiveServiceKind($s),
            'price_variables' => (bool) $s->price_variables,
            'price_fixed_label' => $s->price_fixed_label,
            'pricing' => $this->servicePricingPayload($s),
            'rating' => (float) $s->rating,
            'review_count' => (int) $s->review_count,
            'user_id' => $s->user_id,
            'category_id' => $s->category_id,
        ];

        if ($s->relationLoaded('user') && $s->user) {
            $owner = $s->user;
            $description = trim((string) ($owner->company_description ?? ''));
            if ($description === '') {
                $description = trim((string) ($owner->bio ?? ''));
            }
            $row['owner'] = [
                'id' => $owner->id,
                'name' => $owner->name,
                'profile_type' => $owner->profile_type,
                'company_name' => $owner->company_name,
                'company_address' => $owner->company_address,
                'activity_type' => $owner->activity_type,
                'description' => $description !== '' ? $description : null,
            ];
        }
        if ($s->relationLoaded('category') && $s->category) {
            $row['category'] = [
                'id' => $s->category->id,
                'name' => $s->category->name,
            ];
        }

        if ($detail) {
            $row['created_at'] = $s->created_at?->toIso8601String();
        }

        return $row;
    }
}
