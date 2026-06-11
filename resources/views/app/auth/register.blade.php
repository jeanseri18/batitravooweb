@extends('vitrine.btp_layout')

@section('title', 'Inscription — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Créez votre compte BATITRAVOO — particulier, artisan, entreprise BTP ou fournisseur.')

@section('content')
    @php
        $pt = old('profile_type', $preselectProfile ?? '');
        $showIndividual = in_array($pt, ['particulier', 'artisan'], true);
        $showCompany = in_array($pt, ['entrepreneur_batiment', 'entreprise_fournisseur'], true);
    @endphp

    <div class="auth-page">
        <div class="auth-page__inner">
            <aside class="auth-page__aside" aria-label="Présentation">
                <a href="{{ url('/') }}" class="auth-page__logo">
                    <img src="{{ asset('images/logo.png') }}" alt="BATITRAVOO" width="180" height="54">
                </a>
                <p class="auth-page__aside-eyebrow">Inscription</p>
                <h2 class="auth-page__aside-title">Un compte, votre métier.</h2>
                <p class="auth-page__aside-text">
                    Choisissez votre profil et accédez au marché, aux devis et au support professionnel du bâtiment.
                </p>
                <ul class="auth-page__profiles">
                    <li><a href="{{ route('vitrine.particulier') }}">Particulier</a></li>
                    <li><a href="{{ route('vitrine.artisan') }}">Artisan</a></li>
                    <li><a href="{{ route('vitrine.entreprise_btp') }}">Entreprise BTP</a></li>
                    <li><a href="{{ route('vitrine.fournisseur') }}">Fournisseur</a></li>
                </ul>
            </aside>

            <div class="auth-page__form-wrap">
                <div class="auth-page__card auth-page__card--wide">
                    <h1 class="auth-page__title">Inscription</h1>
                    <p class="auth-page__subtitle">Créez votre compte et choisissez votre profil (identique à l’application).</p>

                    <form method="post" action="{{ route('register.store') }}" id="register-form" class="auth-form" novalidate>
                        @csrf
                        <div class="auth-field">
                            <label for="profile_type">Type de profil</label>
                            <select name="profile_type" id="profile_type" required>
                                <option value="" disabled @selected($pt === '')>— Choisir —</option>
                                <option value="particulier" @selected($pt === 'particulier')>Particulier</option>
                                <option value="artisan" @selected($pt === 'artisan')>Artisan</option>
                                <option value="entrepreneur_batiment" @selected($pt === 'entrepreneur_batiment')>Entrepreneur du bâtiment</option>
                                <option value="entreprise_fournisseur" @selected($pt === 'entreprise_fournisseur')>Entreprise fournisseur</option>
                            </select>
                            <span class="auth-field-hint">Les champs affichés changent selon ce choix (comme l’app mobile).</span>
                            @error('profile_type')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="register-profile-panel" data-register-for="particulier artisan" @if (! $showIndividual) hidden @endif>
                            <div class="auth-field">
                                <label for="name">Nom complet</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" @if ($showIndividual) required @endif>
                                @error('name')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="register-profile-panel" data-register-for="entrepreneur_batiment entreprise_fournisseur" @if (! $showCompany) hidden @endif>
                            <div class="auth-field">
                                <label for="company_name">Raison sociale</label>
                                <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" maxlength="255" autocomplete="organization" @if ($showCompany) required @endif>
                                @error('company_name')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="auth-field">
                                <label for="company_address">Siège / adresse (optionnel)</label>
                                <textarea name="company_address" id="company_address" rows="2" maxlength="2000">{{ old('company_address') }}</textarea>
                                @error('company_address')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="phone">Téléphone (optionnel)</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" maxlength="32" autocomplete="tel">
                            @error('phone')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="auth-field">
                            <label for="email">E-mail de connexion</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="auth-field">
                            <label for="password">Mot de passe</label>
                            <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
                            @error('password')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="auth-field">
                            <label for="password_confirmation">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                        </div>
                        <div class="auth-field auth-field--check auth-field--check-top">
                            <input type="checkbox" name="accept_terms" id="accept_terms" value="1" required {{ old('accept_terms') ? 'checked' : '' }}>
                            <label for="accept_terms">
                                J’accepte les <a href="{{ route('vitrine.terms') }}" target="_blank" rel="noopener noreferrer">conditions générales d’utilisation</a>
                                et la <a href="{{ route('vitrine.privacy') }}" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>.
                            </label>
                        </div>
                        @error('accept_terms')
                            <div class="auth-error">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="auth-submit">Créer mon compte</button>
                    </form>

                    <p class="auth-page__footer">
                        Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
                    </p>
                    @include('vitrine.partials.legal_links')
                    <p class="auth-page__footer">
                        <a href="{{ url('/') }}">← Retour à l’accueil</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
@endpush
