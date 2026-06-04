<?php

namespace App\Http\Controllers\Web\App;

use App\Models\ArtisanBusinessCard;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArtisanBusinessCardWebController extends ShellController
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->profile_type === User::PROFILE_ARTISAN, 403);

        $card = ArtisanBusinessCard::query()->where('user_id', $request->user()->id)->first();

        return $this->render($request, 'artisan_carte_visite', [
            'businessCard' => $card,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $u = $request->user();
        abort_unless($u->profile_type === User::PROFILE_ARTISAN, 403);

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'experience_text' => ['nullable', 'string', 'max:255'],
            'price_mode' => ['nullable', 'string', 'in:fixe,variable,sur_devis'],
            'price_text' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'array', 'max:30'],
            'services.*' => ['string', 'max:500'],
            'location_text' => ['nullable', 'string', 'max:500'],
            'keep_portfolio_paths' => ['nullable'],
            'keep_portfolio_paths.*' => ['string', 'max:500'],
            'portfolio' => ['nullable', 'array', 'max:20'],
            'portfolio.*' => ['file', 'max:15360', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $card = ArtisanBusinessCard::query()->firstOrNew(['user_id' => $u->id]);

        unset($validated['portfolio'], $validated['keep_portfolio_paths'], $validated['price_mode']);
        $card->fill($validated);
        $this->applyPriceMode($card, (string) $request->input('price_mode', 'fixe'), $request->input('price_text'));
        $card->avail_immediate = $request->boolean('avail_immediate');
        $card->avail_appointment = $request->boolean('avail_appointment');
        $card->avail_unavailable = $request->boolean('avail_unavailable');

        if ($request->has('services')) {
            $card->services = array_values(array_filter(
                $request->input('services', []),
                fn ($s) => is_string($s) && trim($s) !== ''
            ));
        }

        $keep = $this->parseKeepPortfolioPaths($request->input('keep_portfolio_paths'));
        $existing = $this->normalizedPortfolioPaths($card);
        $toKeep = array_values(array_intersect($existing, $keep));
        foreach (array_diff($existing, $toKeep) as $path) {
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

        return redirect()->route('app.artisan.business_card')->with('status', 'Carte de visite enregistrée.');
    }

    public function destroy(Request $request): RedirectResponse
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

        return redirect()->route('app.artisan.business_card')->with('status', 'Carte de visite supprimée.');
    }

    /**
     * @return list<UploadedFile>
     */
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

    private function applyPriceMode(ArtisanBusinessCard $card, string $mode, mixed $priceText): void
    {
        $text = is_string($priceText) ? trim($priceText) : '';
        switch ($mode) {
            case 'sur_devis':
                $card->price_on_request = false;
                $card->price_on_quote = true;
                $card->price_text = null;
                break;
            case 'variable':
                $card->price_on_request = false;
                $card->price_on_quote = false;
                $card->price_text = $text === '' ? null : 'À partir de '.$text;
                break;
            default:
                $card->price_on_request = false;
                $card->price_on_quote = false;
                $card->price_text = $text === '' ? null : $text;
                break;
        }
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
     * @return list<string>
     */
    private function normalizedPortfolioPaths(ArtisanBusinessCard $card): array
    {
        $paths = $card->portfolio_paths;
        if (is_array($paths) && $paths !== []) {
            return array_values(array_filter(array_map('strval', $paths)));
        }
        if ($card->portfolio_path) {
            return [(string) $card->portfolio_path];
        }

        return [];
    }
}
