@extends('layouts.app')

@section('title', 'Coupons promotionnels')

@section('content')

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 page-header">
    <div>
        <h3><i class="bi bi-ticket-perforated me-2" style="color:var(--brand);"></i>Coupons promotionnels</h3>
        <p>Gestion des codes partagés par les ambassadeurs.</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i> Nouveau coupon
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Ambassadeur</th>
                    <th>Remise</th>
                    <th>Part acheteur</th>
                    <th>Utilisations</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                    <th class="text-end" style="min-width:180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td><code>{{ $coupon->code }}</code></td>
                        <td>
                            <div style="font-weight:500;">{{ $coupon->ambassador->name }}</div>
                            <div style="font-size:.75rem;color:var(--muted);">{{ $coupon->ambassador->email }}</div>
                        </td>
                        <td class="fw-semibold">
                            @if($coupon->discount_type === 'percentage')
                                {{ $coupon->value }} %
                            @else
                                {{ number_format($coupon->value, 2) }} F
                            @endif
                        </td>
                        <td>
                            <span style="font-weight:500;">{{ $coupon->buyer_share_percent }} %</span>
                            <div style="font-size:.72rem;color:var(--muted);">{{ 100 - $coupon->buyer_share_percent }} % ambassadeur</div>
                        </td>
                        <td>
                            <span style="font-weight:600;">{{ $coupon->usage_count }}</span>
                            <span style="color:var(--muted);font-size:.83rem;">{{ $coupon->usage_limit ? '/ '.$coupon->usage_limit : '/ illim.' }}</span>
                        </td>
                        <td style="font-size:.83rem;color:var(--muted);">
                            @if($coupon->expires_at)
                                {{ $coupon->expires_at->format('d/m/Y') }}
                                @if($coupon->expires_at->isPast())
                                    <div style="font-size:.72rem;color:#a3261d;font-weight:600;">Expiré</div>
                                @endif
                            @else
                                Illimitée
                            @endif
                        </td>
                        <td>
                            @if($coupon->is_active)
                                <span class="gi-badge badge-status-active">Actif</span>
                            @else
                                <span class="gi-badge badge-status-inactive">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">

                                {{-- Activer / Désactiver --}}
                                <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $coupon->is_active ? 'btn-outline-secondary' : 'btn-outline-brand' }}"
                                            style="border-radius:8px;"
                                            title="{{ $coupon->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi {{ $coupon->is_active ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                    </button>
                                </form>

                                {{-- Modifier --}}
                                <a href="{{ route('admin.coupons.edit', $coupon) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   style="border-radius:8px;"
                                   title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Supprimer --}}
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST"
                                      onsubmit="return confirm('Supprimer le coupon {{ $coupon->code }} ?\n\nCette action est irréversible.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            style="border-radius:8px;"
                                            title="Supprimer">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-ticket-perforated"></i></div>
                                <div style="font-weight:600;">Aucun coupon pour le moment</div>
                                <div style="font-size:.8rem;margin-top:.4rem;">
                                    <a href="{{ route('admin.coupons.create') }}" style="color:var(--brand);font-weight:500;">Créer le premier coupon</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-end">{{ $coupons->links() }}</div>

@endsection
