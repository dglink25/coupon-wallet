@extends('layouts.app')

@section('title', 'Nouveau coupon')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-0">Nouveau coupon</h3>
    <p class="text-secondary mb-0">Creer un code promotionnel pour un ambassadeur.</p>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Code du coupon</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-control text-uppercase" placeholder="EX: PROMO25" required>
                    <div class="form-text">Sensible a la casse : "Promo25" et "promo25" sont deux codes differents.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ambassadeur</label>
                    <select name="ambassador_user_id" class="form-select" required>
                        <option value="">-- Choisir un utilisateur --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('ambassador_user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Type de remise</label>
                    <select name="discount_type" class="form-select" required>
                        <option value="percentage" @selected(old('discount_type') === 'percentage')>Pourcentage (%)</option>
                        <option value="fixed" @selected(old('discount_type') === 'fixed')>Montant fixe</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Valeur</label>
                    <input type="number" step="0.01" min="0.01" name="value" value="{{ old('value') }}" class="form-control" required>
                    <div class="form-text">Ex : 10 pour 10 %, ou 1000 pour 1000 F fixe.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Part reversee a l'acheteur (%)</label>
                    <input type="number" min="0" max="100" name="buyer_share_percent" value="{{ old('buyer_share_percent', 50) }}" class="form-control" required>
                    <div class="form-text">Le reste est credite en commission a l'ambassadeur.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Limite d'utilisation</label>
                    <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit') }}" class="form-control" placeholder="Laisser vide = illimite">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date d'expiration</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="form-control">
                    <div class="form-text">Laisser vide = pas d'expiration.</div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-brand">Creer le coupon</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
