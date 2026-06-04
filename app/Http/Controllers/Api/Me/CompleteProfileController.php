<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Api\Concerns\FormatsApiUser;
use App\Http\Controllers\Api\Concerns\StoresUserAvatar;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CompleteProfileController extends Controller
{
    use FormatsApiUser;
    use StoresUserAvatar;

    private const MAX_UPLOAD_KB = 10240;

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $type = $user->profile_type;

        if ($type === null || $type === '') {
            return response()->json(['message' => 'Profil non défini.'], 422);
        }

        $validated = $request->validate($this->rulesForProfile($type, $user));
        $this->assertUploadedDocumentsWhenNeeded($request, $user, $type);

        DB::transaction(function () use ($user, $validated, $request, $type): void {
            $this->applyValidatedUserFields($user, $validated, $type);
            $this->applyProfileCompletionAndValidation($user);
            $user->save();

            $this->syncUploadedDocuments($user, $request, $type);
            if ($request->hasFile('avatar')) {
                $af = $request->file('avatar');
                if ($af !== null) {
                    $this->replaceUserAvatar($user, $af);
                }
            }
        });

        return response()->json([
            'user' => $this->userToArray($user->fresh()),
        ]);
    }

    /**
     * Validation admin : uniquement à la première soumission du profil, ou après refus / demande de corrections.
     * Les mises à jour d’un compte déjà validé ne réinitialisent pas le statut.
     */
    private function applyProfileCompletionAndValidation(User $user): void
    {
        $isFirstCompletion = $user->profile_completed_at === null;
        $wasApproved = $user->profile_validation_status === User::VALIDATION_APPROVED;

        if ($isFirstCompletion) {
            $user->profile_completed_at = now();
            $user->profile_validation_status = User::VALIDATION_PENDING;
            $user->profile_validation_note = null;
            $user->profile_validated_at = null;

            return;
        }

        if ($wasApproved) {
            return;
        }

        $user->profile_validation_status = User::VALIDATION_PENDING;
        $user->profile_validation_note = null;
        $user->profile_validated_at = null;
    }

    /**
     * Si aucun fichier n’est envoyé, une pièce déjà enregistrée pour ce type suffit (mise à jour du profil).
     *
     * @throws ValidationException
     */
    private function assertUploadedDocumentsWhenNeeded(Request $request, User $user, string $type): void
    {
        if ($type === User::PROFILE_ENTREPRENEUR_BATIMENT || $type === User::PROFILE_ENTREPRISE_FOURNISSEUR) {
            return;
        }

        $requiredFields = match ($type) {
            User::PROFILE_PARTICULIER => ['document_cni', 'document_other'],
            User::PROFILE_ARTISAN => ['document_cni', 'document_certificate'],
            default => [],
        };

        $errors = [];
        foreach ($requiredFields as $field) {
            if ($request->hasFile($field)) {
                continue;
            }
            $kind = match ($field) {
                'document_cni' => UserDocument::KIND_CNI,
                'document_other' => UserDocument::KIND_OTHER,
                'document_certificate' => UserDocument::KIND_CERTIFICATE,
                default => null,
            };
            if ($kind === null || ! $user->documents()->where('kind', $kind)->exists()) {
                $errors[$field] = ['Ce document est obligatoire.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function rulesForProfile(string $type, User $user): array
    {
        $isUpdate = $user->profile_completed_at !== null;
        $file = $isUpdate
            ? ['nullable', 'file', 'max:'.self::MAX_UPLOAD_KB]
            : ['required', 'file', 'max:'.self::MAX_UPLOAD_KB];
        $fileNullable = ['nullable', 'file', 'max:'.self::MAX_UPLOAD_KB];

        return match ($type) {
            User::PROFILE_PARTICULIER => [
                'name' => ['required', 'string', 'max:255'],
                'bio' => ['required', 'string', 'max:5000'],
                'ville' => ['required', 'string', 'max:255'],
                'commune' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string', 'max:2000'],
                'phone' => ['required', 'string', 'max:32'],
                'contact_email' => ['required', 'email', 'max:255'],
                'document_cni' => $file,
                'document_other' => $file,
            ],
            User::PROFILE_ARTISAN => [
                'name' => ['required', 'string', 'max:255'],
                'bio' => ['required', 'string', 'max:5000'],
                'ville' => ['required', 'string', 'max:255'],
                'commune' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string', 'max:2000'],
                'phone' => ['required', 'string', 'max:32'],
                'contact_email' => ['required', 'email', 'max:255'],
                'artisan_availability' => ['required', 'string', Rule::in(['immediate', 'appointment', 'unavailable'])],
                'document_cni' => $file,
                'document_certificate' => $file,
            ],
            User::PROFILE_ENTREPRISE_FOURNISSEUR => [
                'company_name' => ['required', 'string', 'max:255'],
                'company_description' => ['required', 'string', 'max:5000'],
                'ville' => ['required', 'string', 'max:255'],
                'commune' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string', 'max:2000'],
                'phone' => ['required', 'string', 'max:32'],
                'contact_email' => ['required', 'email', 'max:255'],
                'manager_name' => ['required', 'string', 'max:255'],
                'manager_contact' => ['required', 'string', 'max:255'],
                'avatar' => $fileNullable,
                'document_manager_cni' => $fileNullable,
                'document_commerce_register' => $fileNullable,
                'document_dfe' => $fileNullable,
            ],
            User::PROFILE_ENTREPRENEUR_BATIMENT => [
                'company_name' => ['required', 'string', 'max:255'],
                'company_description' => ['required', 'string', 'max:5000'],
                'years_experience' => ['required', 'string', 'max:64'],
                'activity_type' => ['required', 'string', 'max:128'],
                'company_size' => ['required', 'string', 'max:128'],
                'ville' => ['required', 'string', 'max:255'],
                'commune' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string', 'max:2000'],
                'phone' => ['required', 'string', 'max:32'],
                'contact_email' => ['required', 'email', 'max:255'],
                'manager_name' => ['required', 'string', 'max:255'],
                'manager_contact' => ['required', 'string', 'max:255'],
                'document_commerce_register' => $fileNullable,
                'document_dfe' => $fileNullable,
                'document_manager_cni' => $fileNullable,
            ],
            default => abort(422, 'Type de profil inconnu.'),
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function applyValidatedUserFields(User $user, array $validated, string $type): void
    {
        $skip = [
            'document_cni',
            'document_other',
            'document_certificate',
            'document_commerce_register',
            'document_manager_cni',
            'document_dfe',
            'avatar',
        ];

        foreach ($skip as $k) {
            unset($validated[$k]);
        }

        if ($type === User::PROFILE_PARTICULIER || $type === User::PROFILE_ARTISAN) {
            $user->name = $validated['name'];
            $user->bio = $validated['bio'];
            $user->city = $validated['ville'];
            $user->commune = $validated['commune'];
            $user->company_address = $validated['company_address'];
            $user->phone = $validated['phone'];
            $user->contact_email = $validated['contact_email'];
            if ($type === User::PROFILE_ARTISAN) {
                $user->artisan_availability = $validated['artisan_availability'];
            }

            return;
        }

        if ($type === User::PROFILE_ENTREPRISE_FOURNISSEUR) {
            $user->name = $validated['company_name'];
            $user->company_name = $validated['company_name'];
            $user->company_description = $validated['company_description'];
            $user->city = $validated['ville'];
            $user->commune = $validated['commune'];
            $user->company_address = $validated['company_address'];
            $user->phone = $validated['phone'];
            $user->contact_email = $validated['contact_email'];
            $user->manager_name = $validated['manager_name'];
            $user->manager_contact = $validated['manager_contact'];

            return;
        }

        if ($type === User::PROFILE_ENTREPRENEUR_BATIMENT) {
            $user->name = $validated['company_name'];
            $user->company_name = $validated['company_name'];
            $user->company_description = $validated['company_description'];
            $user->years_experience = $validated['years_experience'];
            $user->activity_type = $validated['activity_type'];
            $user->company_size = $validated['company_size'];
            $user->city = $validated['ville'];
            $user->commune = $validated['commune'];
            $user->company_address = $validated['company_address'];
            $user->phone = $validated['phone'];
            $user->contact_email = $validated['contact_email'];
            $user->manager_name = $validated['manager_name'];
            $user->manager_contact = $validated['manager_contact'];
        }
    }

    private function syncUploadedDocuments(User $user, Request $request, string $type): void
    {
        /** @var array<string, string> $mapping input field => UserDocument kind */
        $mapping = match ($type) {
            User::PROFILE_PARTICULIER => [
                'document_cni' => UserDocument::KIND_CNI,
                'document_other' => UserDocument::KIND_OTHER,
            ],
            User::PROFILE_ARTISAN => [
                'document_cni' => UserDocument::KIND_CNI,
                'document_certificate' => UserDocument::KIND_CERTIFICATE,
            ],
            User::PROFILE_ENTREPRISE_FOURNISSEUR => [
                'document_manager_cni' => UserDocument::KIND_MANAGER_CNI,
                'document_commerce_register' => UserDocument::KIND_COMMERCE_REGISTER,
                'document_dfe' => UserDocument::KIND_DFE,
            ],
            User::PROFILE_ENTREPRENEUR_BATIMENT => [
                'document_commerce_register' => UserDocument::KIND_COMMERCE_REGISTER,
                'document_dfe' => UserDocument::KIND_DFE,
                'document_manager_cni' => UserDocument::KIND_MANAGER_CNI,
            ],
            default => [],
        };

        foreach ($mapping as $field => $kind) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            if ($file === null) {
                continue;
            }

            UserDocument::storeUploaded($user, $file, $kind);
        }
    }
}
