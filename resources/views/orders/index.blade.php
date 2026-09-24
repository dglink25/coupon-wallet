@extends('layouts.app')

@section('title', 'Mes commandes')

@section('content')
<div class="d-flex justify-content-between align-items-center page-header">
    <div>
        <h3 class="fw-bold mb-0">Mes commandes</h3>
        <p class="text-secondary mb-0">Commandes simulees, avec ou sans code coupon.</p>
    </div>
    <a href="{{ route('orders.create') }}" class="btn btn-brand">+ Nouvelle commande</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Montant</th>
                    <th>Coupon</th>
                    <th>Remise</th>
                    <th>Net a payer</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ number_format($order->amount, 2) }} F</td>
                        <td>{{ $order->coupon?->code ?? '—' }}</td>
                        <td>{{ $order->discount_amount > 0 ? '-'.number_format($order->discount_amount, 2).' F' : '—' }}</td>
                        <td class="fw-semibold">{{ number_format($order->netAmount(), 2) }} F</td>
                        <td>
                            @if ($order->status === 'paid')
                                <span class="badge badge-status-paid">Payee</span>
                            @else
                                <span class="badge badge-status-pending">En attente</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($order->status !== 'paid')
                                <form action="{{ route('orders.pay', $order) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Marquer comme payee</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">Aucune commande pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $orders->links() }}</div>
@endsection
