<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Api\Me\MessageController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageThreadWebController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $peerId = (int) $request->query('peer_id', 0);
        if ($peerId <= 0) {
            return response()->json(['message' => 'Contact invalide.'], 422);
        }

        $peer = User::query()->find($peerId);
        if ($peer === null || $peer->isAdmin() || ! $peer->is_active) {
            return response()->json(['message' => 'Contact introuvable.'], 404);
        }

        $sub = $request->duplicate(['peer_id' => $peerId]);
        $response = app(MessageController::class)->index($sub);

        if ($response->getStatusCode() >= 400) {
            return response()->json(['message' => 'Fil indisponible.'], $response->getStatusCode());
        }

        $payload = json_decode($response->getContent(), true);

        return response()->json([
            'peer' => [
                'id' => $peer->id,
                'name' => $peer->name,
                'profile_type' => $peer->profile_type,
            ],
            'messages' => $payload['data'] ?? [],
        ]);
    }
}
