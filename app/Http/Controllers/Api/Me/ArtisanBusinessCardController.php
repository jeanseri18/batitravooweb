<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Api\Concerns\FormatsArtisanBusinessCard;
use App\Http\Controllers\Controller;
use App\Models\ArtisanBusinessCard;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArtisanBusinessCardController extends Controller
{
    use FormatsArtisanBusinessCard;

    public function show(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ARTISAN, 403);

        $c = ArtisanBusinessCard::query()->where('user_id', $u->id)->first();

        return response()->json([
            'data' => $c ? $this->artisanBusinessCardRow($c) : null,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ARTISAN, 403);

        $servicesRaw = $request->input('services');
        if (is_string($servicesRaw) && $servicesRaw !== '') {
            $decoded = json_decode($servicesRaw, true);
            if (is_array($decoded)) {
                $request->merge(['services' => $decoded]);
            }
        }

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'experience_text' => ['nullable', 'string', 'max:255'],
            'price_on_request' => ['sometimes', 'boolean'],
            'price_on_quote' => ['sometimes', 'boolean'],
            'price_text' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'array', 'max:30'],
            'services.*' => ['string', 'max:500'],
            'avail_immediate' => ['sometimes', 'boolean'],
            'avail_appointment' => ['sometimes', 'boolean'],
            'avail_unavailable' => ['sometimes', 'boolean'],
            'location_text' => ['nullable', 'string', 'max:500'],
            'keep_portfolio_paths' => ['nullable', 'string', 'max:8000'],
        ]);

        $this->validatePortfolioUploads($request);

        $card = ArtisanBusinessCard::query()->firstOrNew(['user_id' => $u->id]);
        unset($validated['portfolio'], $validated['keep_portfolio_paths']);
        $card->fill($validated);
        if (array_key_exists('services', $validated)) {
            $card->services = $validated['services'] ?? [];
        }

        $keep = $this->parseKeepPortfolioPaths($request->input('keep_portfolio_paths'));
        $existing = $this->normalizedPortfolioPaths($card);
        $toKeep = array_values(array_intersect($existing, $keep));
        $removed = array_diff($existing, $toKeep);
        foreach ($removed as $path) {
            Storage::disk('public')->delete($path);
        }

        $newPaths = $toKeep;
        foreach ($this->collectPortfolioUploads($request) as $pf) {
            if ($pf !== null && $pf->isValid()) {
                $newPaths[] = $pf->store('artisan_portfolio/'.$u->id, 'public');
            }
        }

        $card->portfolio_paths = $newPaths;
        $card->portfolio_path = $newPaths[0] ?? null;

        $card->user_id = $u->id;
        $card->save();

        return response()->json(['data' => $this->artisanBusinessCardRow($card->fresh())]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ARTISAN, 403);

        $c = ArtisanBusinessCard::query()->where('user_id', $u->id)->first();
        if ($c) {
            foreach ($this->normalizedPortfolioPaths($c) as $path) {
                Storage::disk('public')->delete($path);
            }
            $c->delete();
        }

        return response()->json(['ok' => true]);
    }

    /**
     * @return list<string>
     */
    private function parseKeepPortfolioPaths(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }
        if (is_array($raw)) {
            return array_values(array_filter(array_map('strval', $raw)));
        }
        $decoded = json_decode((string) $raw, true);

        return is_array($decoded)
            ? array_values(array_filter(array_map('strval', $decoded)))
            : [];
    }

    /**
     * @return list<UploadedFile>
     */
    private function validatePortfolioUploads(Request $request): void
    {
        $files = $this->collectPortfolioUploads($request);
        if (count($files) > 20) {
            throw ValidationException::withMessages([
                'portfolio' => ['Vous ne pouvez pas envoyer plus de 20 photos.'],
            ]);
        }

        foreach ($files as $file) {
            $validator = Validator::make(
                ['portfolio' => $file],
                ['portfolio' => ['file', 'max:15360', 'mimes:jpg,jpeg,png,webp']],
            );
            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'portfolio' => $validator->errors()->get('portfolio'),
                ]);
            }
        }
    }

    private function collectPortfolioUploads(Request $request): array
    {
        $raw = $request->file('portfolio') ?? $request->file('portfolio[]');
        if ($raw instanceof UploadedFile) {
            return $raw->isValid() ? [$raw] : [];
        }
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(
            $raw,
            fn ($f) => $f instanceof UploadedFile && $f->isValid(),
        ));
    }

}
