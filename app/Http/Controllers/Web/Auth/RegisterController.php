<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('app.home');
        }

        return view('app.auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_type' => [
                'required',
                'string',
                Rule::in([
                    User::PROFILE_ENTREPRENEUR_BATIMENT,
                    User::PROFILE_ENTREPRISE_FOURNISSEUR,
                    User::PROFILE_ARTISAN,
                    User::PROFILE_PARTICULIER,
                ]),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
            'name' => [
                Rule::requiredIf(fn () => in_array((string) $request->input('profile_type'), [
                    User::PROFILE_PARTICULIER,
                    User::PROFILE_ARTISAN,
                ], true)),
                'nullable',
                'string',
                'max:255',
            ],
            'company_name' => [
                Rule::requiredIf(fn () => in_array((string) $request->input('profile_type'), [
                    User::PROFILE_ENTREPRENEUR_BATIMENT,
                    User::PROFILE_ENTREPRISE_FOURNISSEUR,
                ], true)),
                'nullable',
                'string',
                'max:255',
            ],
            'company_address' => ['nullable', 'string', 'max:2000'],
            'accept_terms' => ['accepted'],
        ], [
            'accept_terms.accepted' => 'Vous devez accepter les conditions générales et la politique de confidentialité.',
        ]);

        $isIndividual = in_array($data['profile_type'], [
            User::PROFILE_PARTICULIER,
            User::PROFILE_ARTISAN,
        ], true);

        $displayName = $isIndividual
            ? (string) ($data['name'] ?? '')
            : (string) ($data['company_name'] ?? '');

        $user = User::query()->create([
            'name' => $displayName,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_USER,
            'profile_type' => $data['profile_type'],
            'phone' => $data['phone'] ?? null,
            'company_name' => $isIndividual ? ($data['company_name'] ?? null) : ($data['company_name'] ?? null),
            'company_address' => $data['company_address'] ?? null,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('app.home')->with('status', 'Bienvenue ! Complétez votre profil depuis l’espace applicatif.');
    }
}
