@extends('layouts.app')

@section('title', 'Mon portefeuille')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-0">Mon portefeuille</h3>
    <p class="text-secondary mb-0">Solde disponible et historique de vos transactions.</p>
</div>

<div class="card mb-4">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="text-secondary small text-uppercase">Solde disponible</div>
            <div class="wallet-balance">{{ number_format($wallet->balance, 2) }} F</div>
        </div>
        <a href="{{ route('withdrawals.create') }}" class="btn btn-brand">Demander un retrait</a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white border-0 pt-3 fw-semibold">Historique des transactions</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Solde apres</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $tx)
                    <tr>
                        <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($tx->type === 'credit')
                                <span class="badge badge-status-paid">Credit</span>
                            @else
                                <span class="badge badge-status-rejected">Debit</span>
                            @endif
                        </td>
                        <td class="{{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }} fw-semibold">
                            {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} F
                        </td>
                        <td>{{ number_format($tx->balance_after, 2) }} F</td>
                        <td>{{ $tx->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">Aucune transaction pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>
@endsection
