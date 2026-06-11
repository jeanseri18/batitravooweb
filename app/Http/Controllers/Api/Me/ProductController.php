<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ENTREPRISE_FOURNISSEUR, 403);

        $items = Product::query()->where('user_id', $u->id)
            ->with('category')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Product $p) => $this->row($p));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ENTREPRISE_FOURNISSEUR, 403);

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image_path' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:10240'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'stock_units' => ['required', 'integer', 'min:0'],
            'unit_of_measure' => ['nullable', 'string', 'in:'.implode(',', array_keys(Product::unitOptions()))],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $slug = $this->uniqueProductSlug($data['title']);

        $product = Product::query()->create([
            'user_id' => $u->id,
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'price_amount' => $data['price_amount'],
            'stock_units' => $data['stock_units'],
            'unit_of_measure' => $data['unit_of_measure'] ?? Product::UNIT_PIECE,
            'status' => 'approved',
        ]);
        $product->load('category');

        return response()->json(['data' => $this->row($product)], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ENTREPRISE_FOURNISSEUR, 403);
        abort_unless($product->user_id === $u->id, 404);

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image_path' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:10240'],
            'price_amount' => ['sometimes', 'integer', 'min:0'],
            'stock_units' => ['sometimes', 'integer', 'min:0'],
            'unit_of_measure' => ['sometimes', 'string', 'in:'.implode(',', array_keys(Product::unitOptions()))],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        if (array_key_exists('title', $data) && $data['title'] !== $product->title) {
            $product->slug = $this->uniqueProductSlug($data['title'], $product->id);
        }

        $product->fill($data);
        $product->save();
        $product->load('category');

        return response()->json(['data' => $this->row($product)]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ENTREPRISE_FOURNISSEUR, 403);
        abort_unless($product->user_id === $u->id, 404);
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return response()->json(['ok' => true]);
    }

    private function uniqueProductSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'produit';
        $slug = $base;
        $i = 1;
        while (Product::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Product $p): array
    {
        $imageUrl = $p->image_path
            ? storage_public_url($p->image_path)
            : null;

        $r = [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'description' => $p->description,
            'image_path' => $p->image_path,
            'image_url' => $imageUrl,
            'has_image' => $p->image_path !== null && $p->image_path !== '',
            'price_amount' => (int) $p->price_amount,
            'price_display_fr' => number_format((int) $p->price_amount, 0, ',', ' ').' FCFA',
            'stock_units' => (int) $p->stock_units,
            'unit_of_measure' => $p->unit_of_measure ?: Product::UNIT_PIECE,
            'unit_of_measure_label' => $p->unitLabel(),
            'stock_display_fr' => $p->stockShortLabel(),
            'views_count' => (int) ($p->views_count ?? 0),
            'status' => $p->status,
            'category_id' => $p->category_id,
        ];
        if ($p->relationLoaded('category') && $p->category) {
            $r['category'] = [
                'id' => $p->category->id,
                'name' => $p->category->name,
            ];
        }
        $r['created_at'] = $p->created_at?->toIso8601String();

        return $r;
    }
}
