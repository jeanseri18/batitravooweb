<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documents utilisateur (pièces entreprise BTP / fournisseur, etc.).
 */
class UserDocumentController extends Controller
{
    /**
     * @return list<string>
     */
    public static function companyComplianceKinds(): array
    {
        return [
            UserDocument::KIND_COMMERCE_REGISTER,
            UserDocument::KIND_DFE,
            UserDocument::KIND_MANAGER_CNI,
        ];
    }

    /**
     * @return list<string>
     */
    public static function artisanComplianceKinds(): array
    {
        return [
            UserDocument::KIND_CNI,
            UserDocument::KIND_CERTIFICATE,
            UserDocument::KIND_OTHER,
        ];
    }

    /**
     * @return list<string>
     */
    private function uploadableKindsFor(User $user): array
    {
        return match ($user->profile_type) {
            User::PROFILE_ENTREPRENEUR_BATIMENT,
            User::PROFILE_ENTREPRISE_FOURNISSEUR => self::companyComplianceKinds(),
            User::PROFILE_ARTISAN => self::artisanComplianceKinds(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function documentPayload(UserDocument $d): array
    {
        $url = $d->storage_path ? storage_public_url($d->storage_path) : null;

        return [
            'id' => $d->id,
            'title' => UserDocument::labelForKind($d->kind),
            'subtitle' => $d->original_filename,
            'kind' => $d->kind,
            'storage_path' => $d->storage_path,
            'file_url' => $url,
            'has_file' => $d->storage_path !== null && $d->storage_path !== '',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $docs = $request->user()->documents()->orderBy('kind')->orderBy('id')->get()->map(
            fn (UserDocument $d) => $this->documentPayload($d),
        );

        return response()->json([
            'data' => $docs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $allowed = $this->uploadableKindsFor($user);
        if ($allowed === []) {
            return response()->json(['message' => 'Dépôt non disponible pour ce type de profil.'], 403);
        }

        $validated = $request->validate([
            'kind' => ['required', 'string', Rule::in($allowed)],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $doc = UserDocument::storeUploaded($user, $request->file('file'), $validated['kind']);

        return response()->json([
            'data' => $this->documentPayload($doc),
        ], 201);
    }

    public function download(Request $request, UserDocument $document): StreamedResponse|JsonResponse
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Document introuvable.'], 404);
        }

        $path = $document->storage_path;
        if ($path === null || $path === '' || ! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        $name = $document->original_filename ?: basename($path);

        return Storage::disk('public')->download($path, $name);
    }

    public function destroy(Request $request, UserDocument $document): JsonResponse
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Document introuvable.'], 404);
        }

        $document->deleteStoredFile();
        $document->delete();

        return response()->json(['message' => 'Document supprimé.']);
    }
}
