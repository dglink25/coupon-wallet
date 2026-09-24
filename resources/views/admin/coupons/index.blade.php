@extends('layouts.app')

@section('title', 'Coupons')

@section('content')
<div class="d-flex justify-content-between align-items-center page-header">
    <div>
        <h3 class="fw-bold mb-0">Coupons</h3>
        <p class="text-secondary mb-0">Gestion des codes promotionnels des ambassadeurs.</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-brand">+ Nouveau coupon</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Ambassadeur</th>
                    <th>Remise</th>
                    <th>Part acheteur</th>
                    <th>Utilisations</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coupons as $coupon)
                    <tr>
                        <td><code class="fw-semibold">{{ $coupon->code }}</code></td>
                        <td>{{ $coupon->ambassador->name }}</td>
                        <td>
                            {{ $coupon->discount_type === 'percentage' ? $coupon->value.' %' : number_format($coupon->value, 2).' F' }}
                        </td>
                        <td>{{ $coupon->buyer_share_percent }} %</td>
                        <td>{{ $coupon->usage_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td>{{ $coupon->expires_at?->format('d/m/Y') ?? 'Illimitee' }}</td>
                        <td>
                            @if ($coupon->is_active)
                                <span class="badge badge-status-paid">Actif</span>
                            @else
                                <span class="badge badge-status-rejected">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">
                                    {{ $coupon->is_active ? 'Desactiver' : 'Activer' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">Aucun coupon pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $coupons->links() }}</div>
@endsection
