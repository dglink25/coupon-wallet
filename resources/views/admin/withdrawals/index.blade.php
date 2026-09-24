@extends('layouts.app')

@section('title', 'Demandes de retrait')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-0">Demandes de retrait</h3>
    <p class="text-secondary mb-0">Approuvez ou rejetez les demandes des ambassadeurs.</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Montant demande</th>
                    <th>Montant approuve</th>
                    <th>Statut</th>
                    <th>Motif</th>
                    <th>Traite par</th>
                    <th style="width: 320px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($withdrawals as $withdrawal)
                    <tr>
                        <td>{{ $withdrawal->user->name }}</td>
                        <td>{{ number_format($withdrawal->requested_amount, 2) }} F</td>
                        <td>{{ $withdrawal->approved_amount !== null ? number_format($withdrawal->approved_amount, 2).' F' : '—' }}</td>
                        <td>
                            <span class="badge badge-status-{{ $withdrawal->status }}">
                                {{ ['pending' => 'En attente', 'approved' => 'Approuvee', 'rejected' => 'Rejetee'][$withdrawal->status] }}
                            </span>
                        </td>
                        <td>{{ $withdrawal->reason ?? '—' }}</td>
                        <td>{{ $withdrawal->processor?->name ?? '—' }}</td>
                        <td>
                            @if ($withdrawal->isPending())
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" class="d-flex gap-1">
                                        @csrf
                                        <input type="number" step="0.01" min="0.01" max="{{ $withdrawal->requested_amount }}"
                                               name="approved_amount" value="{{ $withdrawal->requested_amount }}"
                                               class="form-control form-control-sm" style="width: 100px;" required>
                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Motif (si partiel)" style="width: 140px;">
                                        <button class="btn btn-sm btn-brand">Approuver</button>
                                    </form>
                                    <form action="{{ route('admin.withdrawals.reject', $withdrawal) }}" method="POST" onsubmit="return prepareReject(this)">
                                        @csrf
                                        <input type="hidden" name="reason" class="reject-reason">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Rejeter</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-secondary small">Traitee le {{ $withdrawal->processed_at?->format('d/m/Y H:i') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">Aucune demande de retrait.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $withdrawals->links() }}</div>

<script>
function prepareReject(form) {
    const reason = prompt('Motif du rejet :');
    if (!reason) { return false; }
    form.querySelector('.reject-reason').value = reason;
    return true;
}
</script>
@endsection
