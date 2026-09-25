@extends('layouts.app')

@section('title', 'Nouvelle commande')

@section('content')

{{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <h3><i class="bi bi-plus-circle me-2" style="color:var(--brand);"></i>Nouvelle commande</h3>
    <p>Simulez une commande et appliquez, si vous le souhaitez, un code coupon promotionnel.</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card">
            <div class="card-body p-4 p-lg-5">

                <form method="POST" action="{{ route('orders.store') }}">
                    @csrf

                    {{-- Montant --}}
                    <div class="mb-4">
                        <label class="form-label" for="amount">
                            Montant de la commande
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"
                                  style="background:#fafafa;border:1.5px solid rgba(0,0,0,.12);border-right:none;border-radius:10px 0 0 10px;color:var(--muted);">
                                <i class="bi bi-cash-coin"></i>
                            </span>
                            <input id="amount"
                                   type="number" step="0.01" min="0.01"
                                   name="amount" value="{{ old('amount') }}"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   style="border-left:none;border-radius:0 10px 10px 0;"
                                   required autofocus
                                   placeholder="Ex : 10 000">
                        </div>
                        @error('amount')
                            <div style="color:#a3261d;font-size:.78rem;margin-top:.35rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Code coupon --}}
                    <div class="mb-4">
                        <label class="form-label" for="coupon_code">
                            Code coupon
                            <span style="font-weight:400;color:var(--muted);">(optionnel)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"
                                  style="background:#fafafa;border:1.5px solid rgba(0,0,0,.12);border-right:none;border-radius:10px 0 0 10px;color:var(--muted);">
                                <i class="bi bi-ticket-perforated"></i>
                            </span>
                            <input id="coupon_code"
                                   type="text" name="coupon_code" value="{{ old('coupon_code') }}"
                                   class="form-control @error('coupon_code') is-invalid @enderror"
                                   style="border-left:none;border-radius:0 10px 10px 0;text-transform:none;"
                                   placeholder="Ex : BIENVENUE10">
                        </div>
                        @error('coupon_code')
                            <div style="color:#a3261d;font-size:.78rem;margin-top:.35rem;">{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            La recherche est sensible à la casse : <code>BIENVENUE10</code> ≠ <code>bienvenue10</code>.
                        </div>
                    </div>

                    {{-- Info métier --}}
                    <div style="background:rgba(179,19,74,.04);border:1.5px solid rgba(179,19,74,.12);border-radius:12px;padding:.9rem 1rem;margin-bottom:1.5rem;">
                        <div style="font-size:.78rem;font-weight:600;color:var(--brand);margin-bottom:.4rem;">
                            <i class="bi bi-lightbulb me-1"></i> Comment ça fonctionne
                        </div>
                        <div style="font-size:.78rem;color:var(--muted);line-height:1.55;">
                            La commission de l'ambassadeur n'est créditée que lorsque vous marquez la commande comme
                            <strong>payée</strong>, jamais à la simple création.
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-brand flex-grow-1">
                            <i class="bi bi-bag-check me-1"></i> Créer la commande
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary" style="border-radius:10px;">
                            Annuler
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection
