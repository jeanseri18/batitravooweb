<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\FormatsArtisanBusinessCard;
use App\Http\Controllers\Controller;
use App\Models\ArtisanBusinessCard;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublicArtisanBusinessCardController extends Controller
{
    use FormatsArtisanBusinessCard;

    /**
     * Carte de visite publique d’un artisan validé (marketplace).
     */
    public function show(User $user): JsonResponse
    {
        if ($user->profile_type !== User::PROFILE_ARTISAN || ! $user->is_active) {
            return response()->json(['message' => 'Carte introuvable.'], 404);
        }

        if ($user->profile_validation_status === User::VALIDATION_REJECTED) {
            return response()->json(['message' => 'Carte introuvable.'], 404);
        }

        $card = ArtisanBusinessCard::query()->where('user_id', $user->id)->first();
        if ($card === null) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => $this->artisanBusinessCardRow($card),
        ]);
    }
}
