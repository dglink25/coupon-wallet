@extends('layouts.app')

@section('title', 'Modifier le coupon')

@section('content')

<div class="d-flex align-items-center gap-3 page-header">
    <a href="{{ route('admin.coupons.index') }}"
       style="color:var(--muted);text-decoration:none;font-size:.85rem;display:flex;align-items:center;gap:.3rem;">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
    <div>
        <h3 class="mb-0">
            <i class="bi bi-pencil me-2" style="color:var(--brand);"></i>Modifier le coupon
        </h3>
        <p class="mb-0">Code : <code>{{ $coupon->code }}</code></p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-4 p-lg-5">

                <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">

                        {{-- Code --}}
                        <div class="col-md-6">
                            <label class="form-label" for="code">Code du coupon</label>
                            <input id="code" type="text" name="code"
                                   value="{{ old('code', $coupon->code) }}"
                                   class="form-control @error('code') is-invalid @enderror"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Sensible à la casse : <code>PROMO25</code> et <code>promo25</code> sont deux codes distincts.
                            </div>
                        </div>

                        {{-- Ambassadeur --}}
                        <div class="col-md-6">
                            <label class="form-label" for="ambassador_user_id">Ambassadeur</label>
                            <select id="ambassador_user_id" name="ambassador_user_id"
                                    class="form-select @error('ambassador_user_id') is-invalid @enderror" required>
                                <option value="">— Choisir un utilisateur —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                            @selected(old('ambassador_user_id', $coupon->ambassador_user_id) == $user->id)>
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
                                <option value="percentage"
                                        @selected(old('discount_type', $coupon->discount_type) === 'percentage')>
                                    Pourcentage (%)
                                </option>
                                <option value="fixed"
                                        @selected(old('discount_type', $coupon->discount_type) === 'fixed')>
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
                                   name="value" value="{{ old('value', $coupon->value) }}"
                                   class="form-control @error('value') is-invalid @enderror" required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Part acheteur --}}
                        <div class="col-md-4">
                            <label class="form-label" for="buyer_share_percent">Part reversée à l'acheteur (%)</label>
                            <input id="buyer_share_percent" type="number" min="0" max="100"
                                   name="buyer_share_percent"
                                   value="{{ old('buyer_share_percent', $coupon->buyer_share_percent) }}"
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
                                   name="usage_limit"
                                   value="{{ old('usage_limit', $coupon->usage_limit) }}"
                                   class="form-control @error('usage_limit') is-invalid @enderror"
                                   placeholder="Laisser vide = illimité">
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Utilisations à ce jour : <strong>{{ $coupon->usage_count }}</strong></div>
                        </div>

                        {{-- Date d'expiration --}}
                        <div class="col-md-6">
                            <label class="form-label" for="expires_at">Date d'expiration</label>
                            <input id="expires_at" type="date"
                                   name="expires_at"
                                   value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}"
                                   class="form-control @error('expires_at') is-invalid @enderror">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Vide = pas d'expiration.</div>
                        </div>

                        {{-- Info sur les commandes liées --}}
                        @if($coupon->usage_count > 0)
                            <div class="col-12">
                                <div style="background:rgba(179,19,74,.04);border:1.5px solid rgba(179,19,74,.12);border-radius:10px;padding:.85rem 1rem;font-size:.8rem;color:var(--muted);">
                                    <i class="bi bi-info-circle me-1" style="color:var(--brand);"></i>
                                    Ce coupon a déjà été utilisé <strong>{{ $coupon->usage_count }} fois</strong>.
                                    Les commandes passées ne sont pas affectées par cette modification.
                                </div>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="col-12 d-flex gap-2 pt-2">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-floppy me-1"></i> Enregistrer les modifications
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
