@extends('layouts.app')

@section('title', 'Nouveau coupon')

@section('content')

{{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center gap-3 page-header">
    <a href="{{ route('admin.coupons.index') }}"
       style="color:var(--muted);text-decoration:none;font-size:.85rem;display:flex;align-items:center;gap:.3rem;">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
    <div>
        <h3 class="mb-0"><i class="bi bi-plus-circle me-2" style="color:var(--brand);"></i>Nouveau coupon</h3>
        <p class="mb-0">Créer un code promotionnel pour un ambassadeur.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-4 p-lg-5">

                <form method="POST" action="{{ route('admin.coupons.store') }}">
                    @csrf

                    <div class="row g-4">

                        {{-- Code --}}
                        <div class="col-md-6">
                            <label class="form-label" for="code">Code du coupon</label>
                            <input id="code" type="text" name="code"
                                   value="{{ old('code') }}"
                                   class="form-control @error('code') is-invalid @enderror"
                                   placeholder="Ex : PROMO25" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Sensible à la casse : <code>PROMO25</code> ≠ <code>promo25</code>.
                            </div>
                        </div>

                        {{-- Ambassadeur --}}
                        <div class="col-md-6">
                            <label class="form-label" for="ambassador_user_id">Ambassadeur</label>
                            <select id="ambassador_user_id" name="ambassador_user_id"
                                    class="form-select @error('ambassador_user_id') is-invalid @enderror" required>
                                <option value="">— Choisir un utilisateur —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('ambassador_user_id') == $user->id)>
                                        {{ $user->name }} · {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ambassador_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Séparateur --}}
                        <div class="col-12">
                            <hr style="border-color:rgba(0,0,0,.07);margin:0;">
                            <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-top:.75rem;">
                                Paramètres de la remise
                            </div>
                        </div>

                        {{-- Type de remise --}}
                        <div class="col-md-4">
                            <label class="form-label" for="discount_type">Type de remise</label>
                            <select id="discount_type" name="discount_type"
                                    class="form-select @error('discount_type') is-invalid @enderror" required>
                                <option value="percentage" @selected(old('discount_type', 'percentage') === 'percentage')>
                                    Pourcentage (%)
                                </option>
                                <option value="fixed" @selected(old('discount_type') === 'fixed')>
                                    Montant fixe (F)
                                </option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Valeur --}}
                        <div class="col-md-4">
                            <label class="form-label" for="value">Valeur</label>
                            <input id="value" type="number" step="0.01" min="0.01"
                                   name="value" value="{{ old('value') }}"
                                   class="form-control @error('value') is-invalid @enderror" required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Ex : 10 pour 10 % ou 1 000 pour 1 000 F.</div>
                        </div>

                        {{-- Part acheteur --}}
                        <div class="col-md-4">
                            <label class="form-label" for="buyer_share_percent">Part reversée à l'acheteur (%)</label>
                            <input id="buyer_share_percent" type="number" min="0" max="100"
                                   name="buyer_share_percent" value="{{ old('buyer_share_percent', 50) }}"
                                   class="form-control @error('buyer_share_percent') is-invalid @enderror" required>
                            @error('buyer_share_percent')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Le reste va en commission à l'ambassadeur.</div>
                        </div>

                        {{-- Séparateur --}}
                        <div class="col-12">
                            <hr style="border-color:rgba(0,0,0,.07);margin:0;">
                            <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-top:.75rem;">
                                Validité du coupon
                            </div>
                        </div>

                        {{-- Limite d'utilisation --}}
                        <div class="col-md-6">
                            <label class="form-label" for="usage_limit">Limite d'utilisation</label>
                            <input id="usage_limit" type="number" min="1"
                                   name="usage_limit" value="{{ old('usage_limit') }}"
                                   class="form-control @error('usage_limit') is-invalid @enderror"
                                   placeholder="Laisser vide = illimité">
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-infinity me-1"></i>Vide = pas de limite.
                            </div>
                        </div>

                        {{-- Date d'expiration --}}
                        <div class="col-md-6">
                            <label class="form-label" for="expires_at">Date d'expiration</label>
                            <input id="expires_at" type="date"
                                   name="expires_at" value="{{ old('expires_at') }}"
                                   class="form-control @error('expires_at') is-invalid @enderror">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-calendar-x me-1"></i>Vide = pas d'expiration.
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="col-12 d-flex gap-2 pt-2">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-check-circle me-1"></i> Créer le coupon
                            </button>
                            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary" style="border-radius:10px;">
                                Annuler
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection
