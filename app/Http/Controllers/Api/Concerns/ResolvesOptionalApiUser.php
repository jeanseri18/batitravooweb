<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Utilisateur API optionnel sur routes publiques (Bearer token).
 */
trait ResolvesOptionalApiUser
{
    protected function optionalApiUser(Request $request): ?User
    {
        $token = $request->bearerToken();
        if ($token === null || $token === '') {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if ($accessToken === null) {
            return null;
        }

        $user = $accessToken->tokenable;

        return $user instanceof User ? $user : null;
    }

    /**
     * Exclut le compte connecté des listes marketplace (ne pas se voir soi-même).
     */
    protected function excludeSelfFromMarketplace(Builder $query, Request $request, string $column = 'id'): Builder
    {
        $user = $this->optionalApiUser($request);
        if ($user !== null && ! $user->isAdmin()) {
            $query->where($column, '!=', (int) $user->id);
        }

        return $query;
    }
}
