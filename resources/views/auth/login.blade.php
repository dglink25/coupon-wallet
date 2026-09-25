<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion · Coupon Wallet</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand: #b3134a;
            --brand-hover: #8f0f3b;
            --dark: #1c1c28;
            --muted: #6b6b76;
            --surface: #f4f3f8;
            --transition: .25s cubic-bezier(.4,0,.2,1);
        }
        * { box-sizing: border-box; }
        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background-color: var(--surface);
            color: var(--dark);
            margin: 0;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        /* Carte de connexion */
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            animation: fadeUp .4s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0);    }
        }

        /* Logo / Marque */
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand .logo-dot {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: var(--brand);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 16px rgba(179,19,74,.35);
        }
        .login-brand .logo-dot i { color: #fff; font-size: 1.4rem; }
        .login-brand h1 {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -.03em;
            margin: 0;
        }
        .login-brand h1 span { color: var(--brand); }
        .login-brand p {
            font-size: .83rem;
            color: var(--muted);
            margin: .3rem 0 0;
        }

        /* Card */
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.2rem 2rem;
            box-shadow: 0 4px 32px rgba(28,28,40,.1);
        }

        .login-card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 1.5rem;
            letter-spacing: -.02em;
        }

        /* Champs */
        .field-group { margin-bottom: 1.1rem; }
        .field-label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .03em;
            color: var(--dark);
            margin-bottom: .4rem;
        }
        .field-wrap {
            position: relative;
        }
        .field-wrap i {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: .95rem;
            pointer-events: none;
        }
        .field-input {
            width: 100%;
            padding: .6rem .85rem .6rem 2.4rem;
            border: 1.5px solid rgba(0,0,0,.12);
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: var(--dark);
            background: #fafafa;
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
        }
        .field-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3.5px rgba(179,19,74,.12);
            background: #fff;
        }
        .field-input.is-invalid { border-color: #dc3545; }

        /* Bouton principal */
        .btn-login {
            width: 100%;
            background: var(--brand);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
            padding: .7rem;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 1rem;
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
            box-shadow: 0 3px 12px rgba(179,19,74,.3);
        }
        .btn-login:hover {
            background: var(--brand-hover);
            transform: translateY(-1px);
            box-shadow: 0 5px 18px rgba(179,19,74,.4);
        }
        .btn-login:active { transform: translateY(0); }

        /* Alerte d'erreur */
        .alert-login {
            background: #fde8e8;
            color: #a3261d;
            border: none;
            border-radius: 10px;
            padding: .75rem 1rem;
            font-size: .83rem;
            font-weight: 500;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }

        /* Comptes de démo */
        .demo-accounts {
            margin-top: 1.5rem;
            background: rgba(179,19,74,.04);
            border: 1.5px dashed rgba(179,19,74,.2);
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }
        .demo-accounts p {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--brand);
            margin: 0 0 .6rem;
        }
        .demo-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: .78rem;
            padding: .3rem 0;
            border-bottom: 1px solid rgba(179,19,74,.1);
            color: var(--dark);
        }
        .demo-row:last-child { border-bottom: none; padding-bottom: 0; }
        .demo-role {
            font-weight: 600;
            color: var(--muted);
            min-width: 85px;
        }
        .demo-email { color: var(--dark); }
        code {
            background: rgba(179,19,74,.08);
            color: var(--brand);
            padding: .1em .4em;
            border-radius: 5px;
            font-size: .8em;
        }

        @media (max-width: 480px) {
            .login-card { padding: 1.6rem 1.4rem; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">

    {{-- Marque --}}
    <div class="login-brand">
        <div class="logo-dot"><i class="bi bi-wallet2"></i></div>
        <h1>General <span>Invasion</span></h1>
        <p>Système de coupon promotionnel &amp; portefeuille</p>
    </div>

    {{-- Carte de connexion --}}
    <div class="login-card">
        <h2>Connexion à votre espace</h2>

        @if($errors->any())
            <div class="alert-login">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field-group">
                <label class="field-label" for="email">Adresse e-mail</label>
                <div class="field-wrap">
                    <i class="bi bi-envelope"></i>
                    <input id="email" class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           type="email" name="email" value="{{ old('email') }}"
                           autocomplete="email" required autofocus
                           placeholder="vous@exemple.com">
                </div>
            </div>

            <div class="field-group">
                <label class="field-label" for="password">Mot de passe</label>
                <div class="field-wrap">
                    <i class="bi bi-lock"></i>
                    <input id="password" class="field-input"
                           type="password" name="password"
                           autocomplete="current-password" required
                           placeholder="••••••••">
                </div>
            </div>

            <button class="btn-login" type="submit">
                <i class="bi bi-arrow-right-circle me-1"></i> Se connecter
            </button>
        </form>
    </div>

    {{-- Comptes de démonstration --}}
    <div class="demo-accounts">
        <p><i class="bi bi-info-circle me-1"></i>Comptes de démonstration</p>
        <div class="demo-row">
            <span class="demo-role">Administrateur</span>
            <span class="demo-email">admin@generalinvasion.com</span>
            <code>password</code>
        </div>
        <div class="demo-row">
            <span class="demo-role">Ambassadeur</span>
            <span class="demo-email">ambassadeur@generalinvasion.com</span>
            <code>password</code>
        </div>
        <div class="demo-row">
            <span class="demo-role">Acheteur</span>
            <span class="demo-email">acheteur@generalinvasion.com</span>
            <code>password</code>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
