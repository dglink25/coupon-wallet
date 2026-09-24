<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Coupon Wallet') · General Invasion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-pink: #b3134a;
            --brand-dark: #1c1c28;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f6f5f9;
            color: var(--brand-dark);
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .navbar-brand span {
            color: var(--brand-pink);
        }
        .btn-brand {
            background-color: var(--brand-pink);
            border-color: var(--brand-pink);
            color: #fff;
        }
        .btn-brand:hover {
            background-color: #8f0f3b;
            border-color: #8f0f3b;
            color: #fff;
        }
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(28, 28, 40, 0.06);
        }
        .badge-status-pending { background-color: #f0ad4e; }
        .badge-status-paid, .badge-status-approved { background-color: #2fa96b; }
        .badge-status-rejected { background-color: #d9534f; }
        .wallet-balance {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--brand-pink);
        }
        .table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b6b76;
            border-bottom-width: 1px;
        }
        .page-header {
            margin-bottom: 1.75rem;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(28,28,40,0.05);
        }
    </style>
</head>
<body>

@auth
<nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">General <span>Invasion</span> · Wallet</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                @if(auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('admin.coupons.*')) fw-semibold text-dark @endif" href="{{ route('admin.coupons.index') }}">Coupons</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('admin.withdrawals.*')) fw-semibold text-dark @endif" href="{{ route('admin.withdrawals.index') }}">Demandes de retrait</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('wallet.*')) fw-semibold text-dark @endif" href="{{ route('wallet.show') }}">Mon portefeuille</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('orders.*')) fw-semibold text-dark @endif" href="{{ route('orders.index') }}">Mes commandes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('withdrawals.*')) fw-semibold text-dark @endif" href="{{ route('withdrawals.create') }}">Retirer des fonds</a>
                    </li>
                @endif
            </ul>
            <span class="navbar-text me-3 text-secondary small">
                {{ auth()->user()->name }}
                <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">{{ auth()->user()->isAdmin() ? 'Admin' : 'Utilisateur' }}</span>
            </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">Se deconnecter</button>
            </form>
        </div>
    </div>
</nav>
@endauth

<main class="container pb-5">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
