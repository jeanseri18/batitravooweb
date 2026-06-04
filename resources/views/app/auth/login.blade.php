<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — {{ config('app.name') }}</title>
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
                <h2 class="app-auth__hero-title">Votre carnet de chantier, partout.</h2>
                <p class="app-auth__hero-text">Devis, messages, marketplace et support — le même espace que sur l’app mobile.</p>
            </div>
        </div>
        <div class="app-auth__form-col">
        <div class="app-auth__card">
            <h1>Connexion</h1>
            <p class="subtitle">Accédez à votre espace utilisateur Bati Travoo.</p>

            @if (session('status'))
                <div class="app-alert app-alert--success" style="margin-bottom:1rem;">{{ session('status') }}</div>
            @endif

            <form method="post" action="{{ route('login.store') }}">
                @csrf
                <div class="app-field">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <div class="app-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="app-field">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password">
                </div>
                <div class="app-field" style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1.25rem;cursor:pointer;">
                    <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="width:auto;margin:0;cursor:pointer;">
                    <label for="remember" style="margin:0;font-weight:500;cursor:pointer;">Se souvenir de moi</label>
                </div>
                <button type="submit" class="app-btn">Se connecter</button>
            </form>

            <p class="app-auth__footer">
                Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a>
            </p>
            @include('app.partials.legal-links')
            <p class="app-auth__footer" style="margin-top:0.75rem;">
                <a href="{{ url('/') }}">← Retour à la vitrine</a>
                @if (Route::has('admin.login'))
                    · <a href="{{ route('admin.login') }}">Espace administrateur</a>
                @endif
            </p>
        </div>
        </div>
    </div>
</body>
</html>
