<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Api\Me\MessageController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MessageSendController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        $slug = $request->segment(2);

        try {
            $response = app(MessageController::class)->store($request);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => collect($e->errors())->flatten()->first() ?? 'Données invalides.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        }

        if ($response->getStatusCode() >= 400) {
            $payload = json_decode($response->getContent(), true);
            $msg = is_array($payload) && isset($payload['message']) ? (string) $payload['message'] : 'Envoi impossible.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], $response->getStatusCode());
            }

            return back()->withErrors(['body' => $msg])->withInput();
        }

        $payload = json_decode($response->getContent(), true);
        $message = is_array($payload) ? ($payload['data'] ?? null) : null;

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Message envoyé.',
                'data' => $message,
            ], 201);
        }

        $receiverId = (int) $request->input('receiver_id');

        return redirect()
            ->route('app.'.$slug.'.messages', ['peer_id' => $receiverId])
            ->with('status', 'Message envoyé.');
    }
}
