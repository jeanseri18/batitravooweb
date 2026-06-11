@extends('vitrine.btp_layout')

@section('title', 'Connexion — '.config('app.name', 'BATITRAVOO'))
@section('meta_description', 'Connectez-vous à votre espace BATITRAVOO — devis, messages, marketplace et support.')

@section('content')
    <div class="auth-page">
        <div class="auth-page__inner">
            <aside class="auth-page__aside" aria-label="Présentation">
                <a href="{{ url('/') }}" class="auth-page__logo">
                    <img src="{{ asset('images/logo.png') }}" alt="BATITRAVOO" width="180" height="54">
                </a>
                <p class="auth-page__aside-eyebrow">Espace professionnel</p>
                <h2 class="auth-page__aside-title">Votre carnet de chantier, partout.</h2>
                <p class="auth-page__aside-text">
                    Devis, messages, marketplace et support — le même espace que sur l’application mobile.
                </p>
            </aside>

            <div class="auth-page__form-wrap">
                <div class="auth-page__card">
                    <h1 class="auth-page__title">Connexion</h1>
                    <p class="auth-page__subtitle">Accédez à votre espace utilisateur BATITRAVOO.</p>

                    @if (session('status'))
                        <div class="auth-alert auth-alert--success" role="status">{{ session('status') }}</div>
                    @endif

                    <form method="post" action="{{ route('login.store') }}" class="auth-form">
                        @csrf
                        <div class="auth-field">
                            <label for="email">E-mail</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="auth-field">
                            <label for="password">Mot de passe</label>
                            <input type="password" name="password" id="password" required autocomplete="current-password">
                        </div>
                        <div class="auth-field auth-field--check">
                            <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Se souvenir de moi</label>
                        </div>
                        <button type="submit" class="auth-submit">Se connecter</button>
                    </form>

                    <p class="auth-page__footer">
                        Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a>
                    </p>
                    @include('vitrine.partials.legal_links')
                    <p class="auth-page__footer">
                        <a href="{{ url('/') }}">← Retour à l’accueil</a>
                        @if (Route::has('admin.login'))
                            · <a href="{{ route('admin.login') }}">Espace administrateur</a>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
