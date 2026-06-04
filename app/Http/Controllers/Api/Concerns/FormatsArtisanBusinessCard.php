<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\ArtisanBusinessCard;

trait FormatsArtisanBusinessCard
{
    /**
     * @return list<string>
     */
    protected function normalizedPortfolioPaths(ArtisanBusinessCard $c): array
    {
        $paths = $c->portfolio_paths;
        if (is_array($paths) && $paths !== []) {
            return array_values(array_filter(array_map('strval', $paths)));
        }
        if ($c->portfolio_path) {
            return [(string) $c->portfolio_path];
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function artisanBusinessCardRow(ArtisanBusinessCard $c): array
    {
        $c->loadMissing('user');
        $paths = $this->normalizedPortfolioPaths($c);
        $urls = array_values(array_filter(array_map(
            fn (string $p) => storage_public_url($p),
            $paths,
        )));
        $portfolioUrl = $urls[0] ?? null;
        $avatarPath = $c->user?->avatar_path;

        return [
            'id' => $c->id,
            'avatar_url' => $avatarPath ? storage_public_url($avatarPath) : null,
            'display_name' => $c->display_name,
            'profession' => $c->profession,
            'experience_text' => $c->experience_text,
            'price_on_request' => (bool) $c->price_on_request,
            'price_on_quote' => (bool) $c->price_on_quote,
            'price_text' => $c->price_text,
            'services' => $c->services ?? [],
            'avail_immediate' => (bool) $c->avail_immediate,
            'avail_appointment' => (bool) $c->avail_appointment,
            'avail_unavailable' => (bool) $c->avail_unavailable,
            'location_text' => $c->location_text,
            'portfolio_path' => $c->portfolio_path,
            'portfolio_paths' => $paths,
            'portfolio_url' => $portfolioUrl,
            'portfolio_urls' => $urls,
            'has_portfolio' => $urls !== [],
            'updated_at' => $c->updated_at?->toIso8601String(),
        ];
    }
}
