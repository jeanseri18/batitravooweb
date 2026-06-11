<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002744">
    <title>Connexion — Admin {{ config('app.name', 'BATITRAVOO') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ public_asset('css/admin.css') }}?v=11">
    <link rel="stylesheet" href="{{ public_asset('css/admin-vitrine.css') }}?v=2">
</head>
<body class="admin-login-page">
<div class="admin-login">
    <section class="admin-login__hero" aria-labelledby="hero-title">
        <div class="admin-login__hero-inner">
            <div class="admin-login__hero-logo">
                @include('admin.partials.brand-logo')
            </div>
            <p class="admin-login__eyebrow">Back-office</p>
            <h2 id="hero-title">Pilotez l’écosystème<br>BTP &amp; services</h2>
            <p>Utilisateurs, offres, devis, besoins et candidatures : un même socle de données, aligné sur l’application mobile BATITRAVOO.</p>
            <ul class="admin-login__features">
                <li>Modération produits &amp; services</li>
                <li>Suivi des devis &amp; statuts</li>
                <li>Marketplace : besoins &amp; candidatures</li>
                <li>Rapports en temps réel</li>
            </ul>
        </div>
    </section>

    <section class="admin-login__panel" aria-labelledby="login-title">
        <div class="admin-login__box">
            <h1 id="login-title" class="admin-login__title">Connexion administrateur</h1>
            <p class="admin-login__sub">Accédez au back-office BATITRAVOO.</p>

            <form method="post" action="{{ route('admin.login.store') }}" novalidate>
                @csrf

                @if($errors->any())
                    <p class="admin-login__err" role="alert">
                        <svg width="16" height="16" aria-hidden="true"><use href="#admin-ico-x" xlink:href="#admin-ico-x"/></svg>
                        {{ $errors->first() }}
                    </p>
                @endif

                <div class="admin-field">
                    <label for="email" class="admin-field__label">Adresse e-mail</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        autofocus
                        placeholder="vous@batitravoo.com"
                        class="admin-field__control admin-field__input"
                    >
                </div>

                <div class="admin-field">
                    <label for="password" class="admin-field__label">Mot de passe</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="admin-field__control admin-field__input"
                    >
                </div>

                <div class="admin-login__remember">
                    <input type="checkbox" name="remember" value="1" id="r">
                    <label for="r">Se souvenir de moi</label>
                </div>

                <button type="submit" class="admin-login__btn">Se connecter</button>
            </form>

            <p class="admin-login__footer">
                <a class="admin-link" href="{{ url('/') }}">← Retour à la vitrine</a>
            </p>
        </div>
    </section>
</div>

<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
    <symbol id="admin-ico-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 18L18 6M6 6l12 12" />
    </symbol>
</svg>
</body>
</html>
