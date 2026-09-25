@extends('layouts.app')

@section('title', 'Mes commandes')

@section('content')

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 page-header">
    <div>
        <h3><i class="bi bi-bag me-2" style="color:var(--brand);"></i>Mes commandes</h3>
        <p>Commandes simulées, avec ou sans code coupon promotionnel.</p>
    </div>
    <a href="{{ route('orders.create') }}" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle commande
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Montant</th>
                    <th>Coupon</th>
                    <th>Remise</th>
                    <th>Net à payer</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th class="text-end" style="min-width:200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td style="color:var(--muted);font-size:.82rem;font-weight:500;">#{{ $order->id }}</td>
                        <td class="fw-semibold">{{ number_format($order->amount, 2) }} F</td>
                        <td>
                            @if($order->coupon)
                                <code>{{ $order->coupon->code }}</code>
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($order->discount_amount > 0)
                                <span style="color:#155c37;font-weight:600;">
                                    &minus;{{ number_format($order->discount_amount, 2) }} F
                                </span>
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td class="fw-semibold" style="color:var(--brand);">
                            {{ number_format($order->netAmount(), 2) }} F
                        </td>
                        <td style="color:var(--muted);font-size:.82rem;">
                            {{ $order->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            @if($order->status === 'paid')
                                <span class="gi-badge badge-status-paid">Payée</span>
                            @else
                                <span class="gi-badge badge-status-pending">En attente</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">

                                @if($order->status !== 'paid')
                                    {{-- Marquer comme payée --}}
                                    <form action="{{ route('orders.pay', $order) }}" method="POST"
                                          onsubmit="return confirm('Confirmer le paiement de la commande #{{ $order->id }} ?')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-brand" type="submit"
                                                title="Marquer comme payée">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    </form>

                                    {{-- Supprimer --}}
                                    <form action="{{ route('orders.destroy', $order) }}" method="POST"
                                          onsubmit="return confirm('Supprimer définitivement la commande #{{ $order->id }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit"
                                                title="Supprimer">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:.75rem;color:var(--muted);">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        {{ $order->paid_at?->format('d/m/Y') }}
                                    </span>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-bag-x"></i></div>
                                <div style="font-weight:600;">Aucune commande pour le moment</div>
                                <div style="font-size:.8rem;margin-top:.4rem;">
                                    <a href="{{ route('orders.create') }}" style="color:var(--brand);font-weight:500;">
                                        Créer votre première commande simulée
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-end">{{ $orders->links() }}</div>

@endsection
