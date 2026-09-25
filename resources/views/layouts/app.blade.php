<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Coupon Wallet') · General Invasion</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Google Fonts : Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ── Variables ────────────────────────────────────────────────────── */
        :root {
            --brand:        #b3134a;
            --brand-hover:  #8f0f3b;
            --brand-light:  #fdf0f4;
            --dark:         #1c1c28;
            --muted:        #6b6b76;
            --surface:      #f4f3f8;
            --card-radius:  16px;
            --nav-h:        64px;
            --transition:   .25s cubic-bezier(.4,0,.2,1);
        }

        /* ── Base ─────────────────────────────────────────────────────────── */
        *,*::before,*::after { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--surface);
            color: var(--dark);
            font-size: .94rem;
            min-height: 100vh;
        }

        /* ── Navbar ────────────────────────────────────────────────────────── */
        .gi-navbar {
            height: var(--nav-h);
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,.07);
            box-shadow: 0 2px 12px rgba(28,28,40,.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .gi-brand {
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -.03em;
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .45rem;
        }
        .gi-brand:hover { color: var(--dark); }
        .gi-brand .accent { color: var(--brand); }
        .gi-brand .dot    { width: 7px; height: 7px; border-radius: 50%; background: var(--brand); display: inline-block; margin-bottom: 1px; }

        .gi-nav-link {
            color: var(--muted);
            font-weight: 500;
            font-size: .875rem;
            padding: .4rem .75rem;
            border-radius: 8px;
            text-decoration: none;
            transition: color var(--transition), background var(--transition);
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .gi-nav-link:hover,
        .gi-nav-link.active {
            color: var(--brand);
            background: var(--brand-light);
        }

        .badge-role {
            font-size: .68rem;
            font-weight: 600;
            padding: .2em .55em;
            border-radius: 6px;
            letter-spacing: .03em;
            background: rgba(179,19,74,.1);
            color: var(--brand);
        }

        .btn-logout {
            border: 1px solid rgba(0,0,0,.12);
            background: transparent;
            color: var(--muted);
            font-size: .8rem;
            font-weight: 500;
            border-radius: 8px;
            padding: .35rem .75rem;
            transition: all var(--transition);
            cursor: pointer;
        }
        .btn-logout:hover { border-color: var(--brand); color: var(--brand); }

        /* ── Layout principal ─────────────────────────────────────────────── */
        .gi-main {
            padding: 2.5rem 0 4rem;
            animation: fadeUp .35s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0);     }
        }

        /* ── En-tête de page ──────────────────────────────────────────────── */
        .page-header {
            margin-bottom: 1.75rem;
        }
        .page-header h3 {
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: -.02em;
            margin: 0 0 .2rem;
        }
        .page-header p { margin: 0; color: var(--muted); font-size: .875rem; }

        /* ── Cartes ───────────────────────────────────────────────────────── */
        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 2px 18px rgba(28,28,40,.06);
            transition: box-shadow var(--transition);
            overflow: hidden;
        }
        .card:hover { box-shadow: 0 4px 28px rgba(28,28,40,.1); }

        .card-header-clean {
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,.07);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: .9rem;
            color: var(--dark);
        }

        /* ── Boutons ──────────────────────────────────────────────────────── */
        .btn-brand {
            background: var(--brand);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: .875rem;
            border-radius: 10px;
            padding: .5rem 1.1rem;
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
            box-shadow: 0 2px 8px rgba(179,19,74,.25);
        }
        .btn-brand:hover {
            background: var(--brand-hover);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(179,19,74,.35);
        }
        .btn-brand:active { transform: translateY(0); }

        .btn-outline-brand {
            border: 1.5px solid var(--brand);
            color: var(--brand);
            background: transparent;
            font-weight: 600;
            font-size: .875rem;
            border-radius: 10px;
            padding: .45rem 1rem;
            transition: all var(--transition);
        }
        .btn-outline-brand:hover {
            background: var(--brand);
            color: #fff;
        }

        /* ── Tableaux ─────────────────────────────────────────────────────── */
        .table thead th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            border-bottom: 1px solid rgba(0,0,0,.07);
            padding: .85rem 1rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .table tbody td {
            padding: .9rem 1rem;
            border-bottom: 1px solid rgba(0,0,0,.04);
            vertical-align: middle;
        }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr {
            transition: background var(--transition);
        }
        .table tbody tr:hover { background: rgba(179,19,74,.02); }

        /* ── Badges de statut ─────────────────────────────────────────────── */
        .gi-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .04em;
            padding: .28em .7em;
            border-radius: 20px;
        }
        .gi-badge::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .badge-status-pending  { background: #fff3cd; color: #856404; }
        .badge-status-paid,
        .badge-status-approved { background: #d1f0e2; color: #155c37; }
        .badge-status-rejected { background: #fde8e8; color: #a3261d; }
        .badge-status-active   { background: #d1f0e2; color: #155c37; }
        .badge-status-inactive { background: #f0f0f0; color: #5a5a5a; }

        /* ── Alertes flash ────────────────────────────────────────────────── */
        .flash-alert {
            border-radius: 12px;
            border: none;
            font-size: .875rem;
            font-weight: 500;
            padding: .85rem 1.1rem;
            animation: slideDown .3s ease both;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0);    }
        }
        .flash-success { background: #d1f0e2; color: #155c37; }
        .flash-danger  { background: #fde8e8; color: #a3261d; }

        /* ── Formulaires ──────────────────────────────────────────────────── */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid rgba(0,0,0,.12);
            font-size: .875rem;
            padding: .55rem .85rem;
            transition: border-color var(--transition), box-shadow var(--transition);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(179,19,74,.1);
            outline: none;
        }
        .form-label {
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .02em;
            color: var(--dark);
            margin-bottom: .35rem;
        }
        .form-text {
            font-size: .75rem;
            color: var(--muted);
        }

        /* ── Portefeuille ─────────────────────────────────────────────────── */
        .wallet-amount {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--brand);
            letter-spacing: -.04em;
            line-height: 1;
        }
        .wallet-label {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
        }

        /* ── Stat card ────────────────────────────────────────────────────── */
        .stat-card {
            border-radius: var(--card-radius);
            padding: 1.4rem 1.6rem;
            background: #fff;
            box-shadow: 0 2px 18px rgba(28,28,40,.06);
        }
        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -.03em;
        }
        .stat-card .stat-label {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
        }
        .stat-card .stat-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }

        /* ── Code inline ──────────────────────────────────────────────────── */
        code {
            background: rgba(179,19,74,.07);
            color: var(--brand);
            padding: .15em .45em;
            border-radius: 5px;
            font-size: .85em;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
        }

        /* ── Pagination ───────────────────────────────────────────────────── */
        .pagination { gap: .2rem; }
        .page-link {
            border-radius: 8px !important;
            border: 1px solid transparent;
            color: var(--muted);
            font-size: .8rem;
            font-weight: 500;
            padding: .4rem .7rem;
        }
        .page-link:hover   { background: var(--brand-light); color: var(--brand); border-color: transparent; }
        .page-item.active .page-link { background: var(--brand); color: #fff; border-color: var(--brand); }

        /* ── Vide ─────────────────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 3.5rem 2rem;
            color: var(--muted);
        }
        .empty-state .empty-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: .35;
        }

        /* ── Mobile ───────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .gi-main   { padding: 1.5rem 0 3rem; }
            .wallet-amount { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

@auth
{{-- ── Navigation ───────────────────────────────────────────────────────── --}}
<nav class="gi-navbar">
    <div class="container h-100 d-flex align-items-center justify-content-between">

        {{-- Marque --}}
        <a class="gi-brand" href="{{ route('home') }}">
            <span class="dot"></span>
            General <span class="accent">Invasion</span>
            <span style="font-weight:400;color:var(--muted);font-size:.9rem;">· Wallet</span>
        </a>

        {{-- Menu principal --}}
        <ul class="list-unstyled d-none d-md-flex align-items-center gap-1 mb-0">
            @if(auth()->user()->isAdmin())
                <li>
                    <a class="gi-nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"
                       href="{{ route('admin.coupons.index') }}">
                        <i class="bi bi-ticket-perforated"></i> Coupons
                    </a>
                </li>
                <li>
                    <a class="gi-nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}"
                       href="{{ route('admin.withdrawals.index') }}">
                        <i class="bi bi-arrow-down-circle"></i> Retraits
                    </a>
                </li>
            @else
                <li>
                    <a class="gi-nav-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}"
                       href="{{ route('wallet.show') }}">
                        <i class="bi bi-wallet2"></i> Portefeuille
                    </a>
                </li>
                <li>
                    <a class="gi-nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                       href="{{ route('orders.index') }}">
                        <i class="bi bi-bag"></i> Commandes
                    </a>
                </li>
                <li>
                    <a class="gi-nav-link {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}"
                       href="{{ route('withdrawals.create') }}">
                        <i class="bi bi-send"></i> Retirer
                    </a>
                </li>
            @endif
        </ul>

        {{-- Profil + déconnexion --}}
        <div class="d-none d-md-flex align-items-center gap-3">
            <span class="d-flex align-items-center gap-2" style="font-size:.83rem;color:var(--muted);font-weight:500;">
                <i class="bi bi-person-circle" style="font-size:1rem;"></i>
                {{ auth()->user()->name }}
                <span class="badge-role">{{ auth()->user()->isAdmin() ? 'Admin' : 'Membre' }}</span>
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn-logout" type="submit">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </button>
            </form>
        </div>

        {{-- Toggler mobile --}}
        <button class="btn d-md-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
            <i class="bi bi-list" style="font-size:1.4rem;"></i>
        </button>
    </div>

    {{-- Menu mobile --}}
    <div class="collapse d-md-none bg-white border-top" id="mobileNav">
        <div class="container py-2">
            @if(auth()->user()->isAdmin())
                <a class="gi-nav-link d-block mb-1" href="{{ route('admin.coupons.index') }}"><i class="bi bi-ticket-perforated"></i> Coupons</a>
                <a class="gi-nav-link d-block mb-1" href="{{ route('admin.withdrawals.index') }}"><i class="bi bi-arrow-down-circle"></i> Retraits</a>
            @else
                <a class="gi-nav-link d-block mb-1" href="{{ route('wallet.show') }}"><i class="bi bi-wallet2"></i> Portefeuille</a>
                <a class="gi-nav-link d-block mb-1" href="{{ route('orders.index') }}"><i class="bi bi-bag"></i> Commandes</a>
                <a class="gi-nav-link d-block mb-1" href="{{ route('withdrawals.create') }}"><i class="bi bi-send"></i> Retirer</a>
            @endif
            <hr class="my-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn-logout w-100 text-start" type="submit"><i class="bi bi-box-arrow-right"></i> Déconnexion</button>
            </form>
        </div>
    </div>
</nav>
@endauth

<main class="gi-main">
    <div class="container">

        {{-- Alertes flash --}}
        @if(session('status'))
            <div class="flash-alert flash-success d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="flash-alert flash-danger d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="flash-alert flash-danger mb-4">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <ul class="mb-0 ps-0" style="list-style:none;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Masquage automatique des alertes flash après 5 s --}}
<script>
    document.querySelectorAll('.flash-alert').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity .4s ease';
            el.style.opacity    = '0';
            setTimeout(function() { el.remove(); }, 450);
        }, 5000);
    });
</script>
</body>
</html>
