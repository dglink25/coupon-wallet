@extends('layouts.app')

@section('title', 'Demandes de retrait')

@section('content')

{{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <h3><i class="bi bi-arrow-down-circle me-2" style="color:var(--brand);"></i>Demandes de retrait</h3>
    <p>Approuvez ou rejetez les demandes des membres. Le solde n'est débité qu'à l'approbation.</p>
</div>

{{-- ── Tableau ───────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Membre</th>
                    <th>Montant demandé</th>
                    <th>Montant approuvé</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Motif / Traité par</th>
                    <th style="min-width:260px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td style="color:var(--muted);font-size:.82rem;">#{{ $withdrawal->id }}</td>
                        <td>
                            <div style="font-weight:500;">{{ $withdrawal->user->name }}</div>
                            <div style="font-size:.75rem;color:var(--muted);">{{ $withdrawal->user->email }}</div>
                        </td>
                        <td class="fw-semibold">{{ number_format($withdrawal->requested_amount, 2) }} F</td>
                        <td>
                            @if($withdrawal->approved_amount !== null)
                                <span style="color:#155c37;font-weight:600;">
                                    {{ number_format($withdrawal->approved_amount, 2) }} F
                                </span>
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size:.82rem;color:var(--muted);">
                            {{ $withdrawal->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="gi-badge badge-status-{{ $withdrawal->status }}">
                                {{ ['pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Rejetée'][$withdrawal->status] }}
                            </span>
                        </td>
                        <td style="font-size:.82rem;">
                            @if($withdrawal->reason)
                                <div style="color:var(--muted);">{{ $withdrawal->reason }}</div>
                            @endif
                            @if($withdrawal->processor)
                                <div style="font-size:.72rem;color:var(--muted);margin-top:.15rem;">
                                    <i class="bi bi-person-check me-1"></i>{{ $withdrawal->processor->name }}
                                </div>
                            @endif
                            @if(!$withdrawal->reason && !$withdrawal->processor)
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($withdrawal->isPending())
                                <div class="d-flex flex-column gap-2">

                                    {{-- Formulaire d'approbation --}}
                                    <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}"
                                          method="POST" class="d-flex gap-1 align-items-center">
                                        @csrf
                                        <div class="input-group input-group-sm" style="max-width:180px;">
                                            <input type="number" step="0.01" min="0.01"
                                                   max="{{ $withdrawal->requested_amount }}"
                                                   name="approved_amount"
                                                   value="{{ $withdrawal->requested_amount }}"
                                                   class="form-control form-control-sm"
                                                   style="border-radius:8px 0 0 8px;font-size:.8rem;"
                                                   required>
                                            <span class="input-group-text" style="font-size:.78rem;background:#f9f9f9;">F</span>
                                        </div>
                                        <input type="text" name="reason"
                                               class="form-control form-control-sm"
                                               style="border-radius:8px;font-size:.78rem;max-width:110px;"
                                               placeholder="Motif si partiel">
                                        <button class="btn btn-sm btn-brand" type="submit"
                                                title="Approuver"
                                                onclick="return confirm('Approuver cette demande ?')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>

                                    {{-- Bouton de rejet --}}
                                    <form action="{{ route('admin.withdrawals.reject', $withdrawal) }}"
                                          method="POST"
                                          onsubmit="return prepareReject(this)">
                                        @csrf
                                        <input type="hidden" name="reason" class="reject-reason-input">
                                        <button class="btn btn-sm btn-outline-danger w-100"
                                                style="border-radius:8px;font-size:.78rem;font-weight:500;"
                                                type="submit">
                                            <i class="bi bi-x-circle me-1"></i>Rejeter
                                        </button>
                                    </form>

                                </div>
                            @else
                                <span style="font-size:.78rem;color:var(--muted);">
                                    <i class="bi bi-check2-all me-1"></i>
                                    Traitée le {{ $withdrawal->processed_at?->format('d/m/Y à H:i') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                                <div style="font-weight:600;">Aucune demande de retrait</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-end">
    {{ $withdrawals->links() }}
</div>

<script>
function prepareReject(form) {
    const reason = prompt('Veuillez saisir le motif du rejet :');
    if (!reason || reason.trim() === '') {
        alert('Un motif est obligatoire pour rejeter une demande.');
        return false;
    }
    form.querySelector('.reject-reason-input').value = reason.trim();
    return true;
}
</script>

@endsection
