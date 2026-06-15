<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesOptionalApiUser;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    use ResolvesOptionalApiUser;

    public function index(Request $request): JsonResponse
    {
        $q = Product::query()
            ->where(function ($statusQuery) {
                $statusQuery->where('status', 'approved')
                    ->orWhere(function ($pending) {
                        $pending->where('status', 'pending')
                            ->whereHas('user', function ($u) {
                                $u->where('profile_type', User::PROFILE_ENTREPRISE_FOURNISSEUR)
                                    ->where('profile_validation_status', User::VALIDATION_APPROVED)
                                    ->where('is_active', true);
                            });
                    });
            })
            ->whereHas('user', function ($b) {
                $b->where('profile_type', User::PROFILE_ENTREPRISE_FOURNISSEUR)
                    ->where('is_active', true);
            })
            ->with(['category', 'user']);

        if (! $request->filled('user_id')) {
            $this->excludeSelfFromMarketplace($q, $request, 'user_id');
        }

        $search = $request->string('q')->trim()->toString();
        if ($search !== '') {
            $q->where(function ($b) use ($search) {
                $b->where('title', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('company_name', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('category_id')) {
            $q->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('user_id')) {
            $uid = (int) $request->input('user_id');
            if ($uid > 0) {
                $q->where('user_id', $uid);
            }
        }

        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $paginated = $q->orderByDesc('id')->paginate($perPage);

        $paginated->getCollection()->transform(function (Product $p) {
            return $this->toRow($p);
        });

        return response()->json($paginated);
    }

    public function show(Product $product): JsonResponse
    {
        if (! $this->isPubliclyVisible($product)) {
            return response()->json(['message' => 'Non trouvé.'], 404);
        }

        $product->load(['category', 'user']);
        $product->increment('views_count');

        return response()->json(['data' => $this->toRow($product, true)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(Product $p, bool $detail = false): array
    {
        $imageUrl = $p->image_path
            ? storage_public_url($p->image_path)
            : null;

        $base = [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'description' => $p->description,
            'image_path' => $p->image_path,
            'image_url' => $imageUrl,
            'has_image' => $p->image_path !== null && $p->image_path !== '',
            'price_amount' => (int) $p->price_amount,
            'price_display_fr' => (int) $p->price_amount > 0
                ? number_format((int) $p->price_amount, 0, ',', ' ').' FCFA'
                : 'Sur devis',
            'stock_units' => (int) $p->stock_units,
            'unit_of_measure' => $p->unit_of_measure ?: Product::UNIT_PIECE,
            'unit_of_measure_label' => $p->unitLabel(),
            'stock_display_fr' => $p->stockShortLabel(),
            'views_count' => (int) $p->views_count,
            'user_id' => $p->user_id,
            'category_id' => $p->category_id,
        ];

        if ($p->relationLoaded('user') && $p->user) {
            $owner = $p->user;
            $description = trim((string) ($owner->company_description ?? ''));
            if ($description === '') {
                $description = trim((string) ($owner->bio ?? ''));
            }
            $base['owner'] = [
                'id' => $owner->id,
                'name' => $owner->name,
                'display_name' => $owner->marketplaceDisplayName(),
                'profile_type' => $owner->profile_type,
                'company_name' => $owner->company_name,
                'company_address' => $owner->company_address,
                'activity_type' => $owner->activity_type,
                'description' => $description !== '' ? $description : null,
            ];
        }
        if ($p->relationLoaded('category') && $p->category) {
            $base['category'] = [
                'id' => $p->category->id,
                'name' => $p->category->name,
                'slug' => $p->category->slug,
            ];
        } elseif (! $detail) {
            $base['category'] = null;
        }

        if ($detail) {
            $base['created_at'] = $p->created_at?->toIso8601String();
        }

        return $base;
    }

    private function isPubliclyVisible(Product $product): bool
    {
        if ($product->status === 'approved') {
            return true;
        }

        if ($product->status !== 'pending') {
            return false;
        }

        $owner = $product->user;
        if ($owner === null) {
            return false;
        }

        return $owner->profile_type === User::PROFILE_ENTREPRISE_FOURNISSEUR
            && $owner->profile_validation_status === User::VALIDATION_APPROVED
            && $owner->is_active;
    }
}
