<?php

namespace App\Http\Controllers\Web\App;

use App\Models\Product;
use App\Models\User;
use App\Services\Web\MeApiBridge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FournisseurProductWebController extends ShellController
{
    public function create(Request $request): View
    {
        $categories = app(MeApiBridge::class)->categoriesForProducts($request);

        return $this->render($request, 'product_form', [
            'productFormMode' => 'create',
            'formProduct' => null,
            'categories' => $categories,
            'title' => 'Nouveau produit',
            'intro' => 'Ajoutez une référence avec photo, catégorie, prix et stock — publication après modération, comme sur l’application mobile.',
        ]);
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeProduct($request, $product);
        $categories = app(MeApiBridge::class)->categoriesForProducts($request);

        return $this->render($request, 'product_form', [
            'productFormMode' => 'edit',
            'formProduct' => $product,
            'categories' => $categories,
            'title' => 'Modifier le produit',
            'intro' => 'Mettez à jour votre catalogue — les changements majeurs peuvent être revus en modération.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ENTREPRISE_FOURNISSEUR, 403);

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'max:10240'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'stock_units' => ['required', 'integer', 'min:0'],
            'unit_of_measure' => ['nullable', 'string', 'in:'.implode(',', array_keys(Product::unitOptions()))],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $slug = $this->uniqueProductSlug($data['title']);

        Product::query()->create([
            'user_id' => $u->id,
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'image_path' => $imagePath,
            'price_amount' => $data['price_amount'],
            'stock_units' => $data['stock_units'],
            'unit_of_measure' => $data['unit_of_measure'] ?? Product::UNIT_PIECE,
            'status' => 'approved',
        ]);

        return redirect()->route('app.fournisseur.products')->with('status', 'Produit créé et publié sur le marketplace.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'max:10240'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'stock_units' => ['required', 'integer', 'min:0'],
            'unit_of_measure' => ['nullable', 'string', 'in:'.implode(',', array_keys(Product::unitOptions()))],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        if ($data['title'] !== $product->title) {
            $product->slug = $this->uniqueProductSlug($data['title'], $product->id);
        }

        $product->fill([
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price_amount' => $data['price_amount'],
            'stock_units' => $data['stock_units'],
            'unit_of_measure' => $data['unit_of_measure'] ?? Product::UNIT_PIECE,
        ]);
        if (isset($data['image_path'])) {
            $product->image_path = $data['image_path'];
        }
        $product->save();

        return redirect()->route('app.fournisseur.products')->with('status', 'Produit mis à jour.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return redirect()->route('app.fournisseur.products')->with('status', 'Produit supprimé.');
    }

    private function authorizeProduct(Request $request, Product $product): void
    {
        abort_unless($request->user()->profile_type === User::PROFILE_ENTREPRISE_FOURNISSEUR, 403);
        abort_unless((int) $product->user_id === (int) $request->user()->id, 404);
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
}
