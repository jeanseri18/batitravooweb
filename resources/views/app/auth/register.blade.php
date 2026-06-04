<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscription — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-web.css') }}">
</head>
<body class="app-body app-body--auth">
    <div class="app-auth">
        <div class="app-auth__hero">
            <div class="app-auth__hero-inner">
                <div class="app-auth__hero-logo">
                    @include('app.partials.brand-logo', ['variant' => 'inverse', 'size' => 'hero'])
                </div>
                <h2 class="app-auth__hero-title">Un compte, votre métier.</h2>
                <p class="app-auth__hero-text">Choisissez votre profil et accédez au marché, aux devis et au support.</p>
            </div>
        </div>
        <div class="app-auth__form-col">
        <div class="app-auth__card app-auth__card--wide">
            <h1>Inscription</h1>
            <p class="subtitle">Créez votre compte et choisissez votre profil (identique à l’application).</p>

            <form method="post" action="{{ route('register.store') }}" id="register-form" novalidate>
                @csrf
                @php
                    $pt = old('profile_type');
                    $showIndividual = in_array($pt, ['particulier', 'artisan'], true);
                    $showCompany = in_array($pt, ['entrepreneur_batiment', 'entreprise_fournisseur'], true);
                @endphp
                <div class="app-field">
                    <label for="profile_type">Type de profil</label>
                    <select name="profile_type" id="profile_type" required>
                        <option value="" disabled @selected($pt === null || $pt === '')>— Choisir —</option>
                        <option value="particulier" @selected($pt === 'particulier')>Particulier</option>
                        <option value="artisan" @selected($pt === 'artisan')>Artisan</option>
                        <option value="entrepreneur_batiment" @selected($pt === 'entrepreneur_batiment')>Entrepreneur du bâtiment</option>
                        <option value="entreprise_fournisseur" @selected($pt === 'entreprise_fournisseur')>Entreprise fournisseur</option>
                    </select>
                    <span class="app-field-hint">Les champs affichés en dessous changent selon ce choix (comme l’app mobile).</span>
                    @error('profile_type')
                        <div class="app-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="register-profile-panel" data-register-for="particulier artisan" @if (! $showIndividual) hidden @endif>
                    <div class="app-field">
                        <label for="name">Nom complet</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" @if ($showIndividual) required @endif>
                        @error('name')
                            <div class="app-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="register-profile-panel" data-register-for="entrepreneur_batiment entreprise_fournisseur" @if (! $showCompany) hidden @endif>
                    <div class="app-field">
                        <label for="company_name">Raison sociale</label>
                        <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" maxlength="255" autocomplete="organization" @if ($showCompany) required @endif>
                        @error('company_name')
                            <div class="app-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="app-field">
                        <label for="company_address">Siège / adresse (optionnel)</label>
                        <textarea name="company_address" id="company_address" rows="2" maxlength="2000">{{ old('company_address') }}</textarea>
                        @error('company_address')
                            <div class="app-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="app-field">
                    <label for="phone">Téléphone (optionnel)</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" maxlength="32" autocomplete="tel">
                    @error('phone')
                        <div class="app-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="app-field">
                    <label for="email">E-mail de connexion</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')
                        <div class="app-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="app-field">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
                    @error('password')
                        <div class="app-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="app-field">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                </div>
                <div class="app-field" style="display:flex;align-items:flex-start;gap:0.6rem;margin-bottom:1rem;">
                    <input type="checkbox" name="accept_terms" id="accept_terms" value="1" required {{ old('accept_terms') ? 'checked' : '' }} style="width:auto;margin:0.35rem 0 0;cursor:pointer;">
                    <label for="accept_terms" style="margin:0;font-weight:500;cursor:pointer;line-height:1.45;">
                        J’accepte les <a href="{{ route('vitrine.terms') }}" target="_blank" rel="noopener noreferrer">conditions générales d’utilisation</a>
                        et la <a href="{{ route('vitrine.privacy') }}" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>.
                    </label>
                </div>
                @error('accept_terms')
                    <div class="app-error">{{ $message }}</div>
                @enderror
                <button type="submit" class="app-btn">Créer mon compte</button>
            </form>
            <p class="app-auth__footer">
                Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
            </p>
            @include('app.partials.legal-links')
            <p class="app-auth__footer" style="margin-top:0.75rem;">
                <a href="{{ url('/') }}">← Retour à la vitrine</a>
            </p>
        </div>
        </div>
    </div>
    <script>
        (function () {
            var sel = document.getElementById('profile_type');
            if (!sel) return;
            var panels = document.querySelectorAll('.register-profile-panel');
            var nameInput = document.getElementById('name');
            var companyInput = document.getElementById('company_name');

            function applies(panel, value) {
                return panel.getAttribute('data-register-for').split(/\s+/).indexOf(value) !== -1;
            }

            function sync() {
                var v = sel.value || '';
                panels.forEach(function (panel) {
                    var show = v !== '' && applies(panel, v);
                    panel.hidden = !show;
                    panel.querySelectorAll('input[required], textarea[required]').forEach(function (el) {
                        el.required = show;
                    });
                });
                if (nameInput) nameInput.required = v === 'particulier' || v === 'artisan';
                if (companyInput) companyInput.required = v === 'entrepreneur_batiment' || v === 'entreprise_fournisseur';
            }

            sel.addEventListener('change', sync);
            sync();
        })();
    </script>
</body>
</html>
