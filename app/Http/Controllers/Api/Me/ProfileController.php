<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Api\Concerns\FormatsApiUser;
use App\Http\Controllers\Api\Concerns\StoresUserAvatar;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use FormatsApiUser;
    use StoresUserAvatar;

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->syncBusinessIdentity();
        if ($user->isDirty()) {
            $user->save();
        }

        return response()->json([
            'user' => $this->userToArray($user->fresh()),
            'meta' => [
                'labels' => $this->profileUiLabels(),
            ],
        ]);
    }

    /**
     * Libellés affichage web / mobile — alignés sur les constantes du modèle User.
     *
     * @return array<string, array<string, string>>
     */
    private function profileUiLabels(): array
    {
        return [
            'profile_type' => [
                User::PROFILE_PARTICULIER => 'Particulier',
                User::PROFILE_ARTISAN => 'Artisan',
                User::PROFILE_ENTREPRENEUR_BATIMENT => 'Entrepreneur du bâtiment',
                User::PROFILE_ENTREPRISE_FOURNISSEUR => 'Entreprise fournisseur',
            ],
            'profile_validation' => [
                User::VALIDATION_PENDING => 'En attente de validation',
                User::VALIDATION_APPROVED => 'Validé',
                User::VALIDATION_REJECTED => 'Refusé',
                User::VALIDATION_CHANGES_REQUESTED => 'Modifications demandées',
            ],
            'artisan_availability' => [
                'immediate' => 'Disponible immédiatement',
                'appointment' => 'Sur rendez-vous',
                'unavailable' => 'Indisponible',
            ],
        ];
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:2000'],
            'ville' => ['nullable', 'string', 'max:255'],
            'pays' => ['nullable', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'company_description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'years_experience' => ['sometimes', 'nullable', 'string', 'max:64'],
            'activity_type' => ['sometimes', 'nullable', 'string', 'max:128'],
            'company_size' => ['sometimes', 'nullable', 'string', 'max:128'],
            'manager_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'manager_contact' => ['sometimes', 'nullable', 'string', 'max:255'],
            'artisan_availability' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['immediate', 'appointment', 'unavailable']),
            ],
            'profile_type' => [
                'sometimes',
                'string',
                Rule::in([
                    User::PROFILE_ENTREPRENEUR_BATIMENT,
                    User::PROFILE_ENTREPRISE_FOURNISSEUR,
                    User::PROFILE_ARTISAN,
                    User::PROFILE_PARTICULIER,
                ]),
            ],
        ]);

        foreach (
            [
                'phone',
                'company_name',
                'company_address',
                'ville',
                'pays',
                'commune',
                'bio',
                'contact_email',
                'company_description',
                'years_experience',
                'activity_type',
                'company_size',
                'manager_name',
                'manager_contact',
                'artisan_availability',
            ] as $nullableKey
        ) {
            if (array_key_exists($nullableKey, $data) && $data[$nullableKey] === '') {
                $data[$nullableKey] = null;
            }
        }

        $map = [
            'ville' => 'city',
            'pays' => 'country',
            'commune' => 'commune',
        ];
        foreach ($map as $key => $column) {
            if (array_key_exists($key, $data)) {
                $user->{$column} = $data[$key];
                unset($data[$key]);
            }
        }

        $user->fill($data);
        $user->syncBusinessIdentity();
        $user->save();

        return response()->json(['user' => $this->userToArray($user->fresh())]);
    }

    /**
     * Changement de mot de passe (mot de passe actuel vérifié).
     *
     * Corps JSON : current_password, password, password_confirmation
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Mot de passe actuel incorrect.',
            ], 422);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    /**
     * Photo / logo de profil (multipart, champ `avatar`).
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $file = $request->file('avatar');
        if (! $file instanceof UploadedFile) {
            return response()->json(['message' => 'Fichier invalide.'], 422);
        }

        $this->replaceUserAvatar($user, $file);

        return response()->json(['user' => $this->userToArray($user->fresh())]);
    }
}
