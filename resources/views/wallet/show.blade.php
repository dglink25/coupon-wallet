@extends('layouts.app')

@section('title', 'Mon portefeuille')

@section('content')

{{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 page-header">
    <div>
        <h3><i class="bi bi-wallet2 me-2" style="color:var(--brand);"></i>Mon portefeuille</h3>
        <p>Solde disponible et historique complet de vos transactions.</p>
    </div>
    <a href="{{ route('withdrawals.create') }}" class="btn btn-brand">
        <i class="bi bi-send me-1"></i> Demander un retrait
    </a>
</div>

{{-- ── Carte solde ──────────────────────────────────────────────────────── --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #b3134a 0%, #6e0d30 100%); color:#fff;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.1em;opacity:.75;margin-bottom:.4rem;">
                    Solde disponible
                </div>
                <div style="font-size:2.6rem;font-weight:800;letter-spacing:-.04em;line-height:1;">
                    {{ number_format($wallet->balance, 2) }} F
                </div>
                <div style="font-size:.8rem;opacity:.6;margin-top:.4rem;">
                    Portefeuille de {{ auth()->user()->name }}
                </div>
            </div>
            <div style="opacity:.18;font-size:5rem;line-height:1;">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>
</div>

{{-- ── Statistiques rapides ─────────────────────────────────────────────── --}}
@php
    $credits = $transactions->sum(fn($t) => $t->type === 'credit' ? $t->amount : 0);
    $debits  = $transactions->sum(fn($t) => $t->type === 'debit'  ? $t->amount : 0);
@endphp
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Commissions reçues</div>
                    <div class="stat-value mt-1" style="color:var(--brand);">
                        {{ number_format($credits, 2) }} F
                    </div>
                </div>
                <div class="stat-icon" style="background:#d1f0e2;color:#155c37;">
                    <i class="bi bi-arrow-down-left"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total retiré</div>
                    <div class="stat-value mt-1" style="color:#a3261d;">
                        {{ number_format($debits, 2) }} F
                    </div>
                </div>
                <div class="stat-icon" style="background:#fde8e8;color:#a3261d;">
                    <i class="bi bi-arrow-up-right"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Transactions</div>
                    <div class="stat-value mt-1">{{ $transactions->total() }}</div>
                </div>
                <div class="stat-icon" style="background:rgba(179,19,74,.1);color:var(--brand);">
                    <i class="bi bi-activity"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Historique des transactions ──────────────────────────────────────── --}}
<div class="card">
    <div class="card-header-clean d-flex align-items-center gap-2">
        <i class="bi bi-clock-history" style="color:var(--brand);"></i>
        Historique des transactions
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Solde après</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td style="color:var(--muted);font-size:.82rem;">
                            {{ $tx->created_at->format('d/m/Y') }}
                            <div style="font-size:.72rem;opacity:.6;">{{ $tx->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            @if($tx->type === 'credit')
                                <span class="gi-badge badge-status-approved">
                                    <i class="bi bi-arrow-down-left me-1"></i>Crédit
                                </span>
                            @else
                                <span class="gi-badge badge-status-rejected">
                                    <i class="bi bi-arrow-up-right me-1"></i>Débit
                                </span>
                            @endif
                        </td>
                        <td class="fw-semibold" style="color: {{ $tx->type === 'credit' ? '#155c37' : '#a3261d' }}">
                            {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} F
                        </td>
                        <td style="font-weight:500;">{{ number_format($tx->balance_after, 2) }} F</td>
                        <td style="color:var(--muted);font-size:.85rem;">{{ $tx->description ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                                <div style="font-weight:600;">Aucune transaction pour le moment</div>
                                <div style="font-size:.8rem;margin-top:.3rem;">
                                    Vos commissions apparaîtront ici dès qu'une commande sera payée.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-end">
    {{ $transactions->links() }}
</div>

@endsection
