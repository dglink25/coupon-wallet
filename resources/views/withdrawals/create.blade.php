@extends('layouts.app')

@section('title', 'Retirer des fonds')

@section('content')

{{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <h3><i class="bi bi-send me-2" style="color:var(--brand);"></i>Retirer des fonds</h3>
    <p>Votre solde n'est débité qu'après validation par un administrateur.</p>
</div>

<div class="row g-4">

    {{-- ── Formulaire de demande ──────────────────────────────────────── --}}
    <div class="col-md-5">

        {{-- Solde disponible --}}
        <div class="card mb-3" style="background:linear-gradient(135deg,#b3134a,#6e0d30);color:#fff;">
            <div class="card-body p-4">
                <div style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.1em;opacity:.7;margin-bottom:.3rem;">
                    Solde disponible
                </div>
                <div style="font-size:2.2rem;font-weight:800;letter-spacing:-.04em;line-height:1;">
                    {{ number_format($wallet->balance, 2) }} F
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <h6 style="font-weight:700;margin-bottom:1.2rem;">Nouvelle demande de retrait</h6>

                <form method="POST" action="{{ route('withdrawals.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="amount">Montant à retirer</label>
                        <div class="input-group">
                            <span class="input-group-text"
                                  style="background:#fafafa;border:1.5px solid rgba(0,0,0,.12);border-right:none;border-radius:10px 0 0 10px;color:var(--muted);">
                                <i class="bi bi-cash-coin"></i>
                            </span>
                            <input id="amount"
                                   type="number" step="0.01" min="0.01" max="{{ $wallet->balance }}"
                                   name="amount" value="{{ old('amount') }}"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   style="border-left:none;border-radius:0 10px 10px 0;"
                                   required>
                        </div>
                        @error('amount')
                            <div style="color:#a3261d;font-size:.78rem;margin-top:.35rem;">{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-1">
                            Maximum : {{ number_format($wallet->balance, 2) }} F
                        </div>
                    </div>

                    {{-- Info --}}
                    <div style="background:rgba(179,19,74,.04);border:1.5px solid rgba(179,19,74,.12);border-radius:10px;padding:.8rem;margin-bottom:1.2rem;font-size:.78rem;color:var(--muted);">
                        <i class="bi bi-shield-check me-1" style="color:var(--brand);"></i>
                        Votre solde reste intact jusqu'à l'approbation par l'administrateur.
                        Aucun débit n'est effectué à la simple demande.
                    </div>

                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-send me-1"></i> Envoyer la demande
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Historique des demandes ─────────────────────────────────────── --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header-clean d-flex align-items-center gap-2">
                <i class="bi bi-clock-history" style="color:var(--brand);"></i>
                Historique de mes demandes
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Demandé</th>
                            <th>Approuvé</th>
                            <th>Statut</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $w)
                            <tr>
                                <td style="color:var(--muted);font-size:.82rem;">
                                    {{ $w->created_at->format('d/m/Y') }}
                                </td>
                                <td class="fw-semibold">{{ number_format($w->requested_amount, 2) }} F</td>
                                <td>
                                    @if($w->approved_amount !== null)
                                        <span class="fw-semibold" style="color:#155c37;">
                                            {{ number_format($w->approved_amount, 2) }} F
                                        </span>
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="gi-badge badge-status-{{ $w->status }}">
                                        {{ ['pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Rejetée'][$w->status] }}
                                    </span>
                                </td>
                                <td style="color:var(--muted);font-size:.82rem;">
                                    {{ $w->reason ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                                        <div style="font-weight:600;">Aucune demande pour le moment</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-end">{{ $history->links() }}</div>
    </div>

</div>

@endsection
