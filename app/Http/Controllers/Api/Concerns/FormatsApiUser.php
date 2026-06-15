<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\User;

trait FormatsApiUser
{
    /**
     * @return array<string, mixed>
     */
    protected function userToArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'display_name' => $user->marketplaceDisplayName(),
            'email' => $user->email,
            'profile_type' => $user->profile_type,
            'phone' => $user->phone,
            'company_name' => $user->company_name,
            'company_address' => $user->company_address,
            'ville' => $user->city,
            'pays' => $user->country,
            'commune' => $user->commune,
            'bio' => $user->bio,
            'contact_email' => $user->contact_email,
            'company_description' => $user->company_description,
            'years_experience' => $user->years_experience,
            'activity_type' => $user->activity_type,
            'company_size' => $user->company_size,
            'manager_name' => $user->manager_name,
            'manager_contact' => $user->manager_contact,
            'artisan_availability' => $user->artisan_availability,
            'profile_completed_at' => $user->profile_completed_at?->toIso8601String(),
            'profile_validation_status' => $user->profile_validation_status ?? User::VALIDATION_APPROVED,
            'profile_validation_note' => $user->profile_validation_note,
            'profile_validated_at' => $user->profile_validated_at?->toIso8601String(),
            'avatar_url' => $user->avatar_path
                ? storage_public_url($user->avatar_path)
                : null,
            'is_active' => (bool) $user->is_active,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
