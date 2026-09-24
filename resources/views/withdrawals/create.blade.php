@extends('layouts.app')

@section('title', 'Retirer des fonds')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-0">Retirer des fonds</h3>
    <p class="text-secondary mb-0">Votre solde n'est debite qu'apres validation par un administrateur.</p>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-secondary small text-uppercase">Solde disponible</div>
                <div class="wallet-balance mb-3">{{ number_format($wallet->balance, 2) }} F</div>

                <form method="POST" action="{{ route('withdrawals.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Montant a retirer</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $wallet->balance }}" name="amount" value="{{ old('amount') }}" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-brand w-100">Envoyer la demande</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white border-0 pt-3 fw-semibold">Historique de mes demandes</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Demande</th>
                            <th>Approuve</th>
                            <th>Statut</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $w)
                            <tr>
                                <td>{{ $w->created_at->format('d/m/Y') }}</td>
                                <td>{{ number_format($w->requested_amount, 2) }} F</td>
                                <td>{{ $w->approved_amount !== null ? number_format($w->approved_amount, 2).' F' : '—' }}</td>
                                <td><span class="badge badge-status-{{ $w->status }}">
                                    {{ ['pending' => 'En attente', 'approved' => 'Approuvee', 'rejected' => 'Rejetee'][$w->status] }}
                                </span></td>
                                <td>{{ $w->reason ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">Aucune demande pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $history->links() }}</div>
    </div>
</div>
@endsection
