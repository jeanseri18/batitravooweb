<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Api\Me\UserDocumentController as ApiUserDocumentController;
use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserDocumentWebController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $response = app(ApiUserDocumentController::class)->store($request);

        if ($response->getStatusCode() >= 400) {
            $payload = json_decode($response->getContent(), true);

            return redirect()->back()->withInput()->withErrors([
                'document_upload' => is_array($payload) && isset($payload['message'])
                    ? (string) $payload['message']
                    : 'Impossible de déposer le document.',
            ]);
        }

        return redirect()->back()->with('status', 'Document déposé.');
    }

    public function destroy(Request $request, UserDocument $document): RedirectResponse
    {
        $response = app(ApiUserDocumentController::class)->destroy($request, $document);

        if ($response->getStatusCode() >= 400) {
            $payload = json_decode($response->getContent(), true);

            return redirect()->back()->withErrors([
                'document_upload' => is_array($payload) && isset($payload['message'])
                    ? (string) $payload['message']
                    : 'Impossible de supprimer le document.',
            ]);
        }

        return redirect()->back()->with('status', 'Document supprimé.');
    }
}
