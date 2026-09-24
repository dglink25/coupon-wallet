@extends('layouts.app')

@section('title', 'Nouvelle commande')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-0">Nouvelle commande</h3>
    <p class="text-secondary mb-0">Simulez une commande et appliquez, si vous le souhaitez, un code coupon.</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('orders.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Montant de la commande</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Code coupon (optionnel)</label>
                        <input type="text" name="coupon_code" value="{{ old('coupon_code') }}" class="form-control" placeholder="Ex: BIENVENUE10">
                        <div class="form-text">La recherche est sensible a la casse.</div>
                    </div>

                    <button type="submit" class="btn btn-brand w-100">Creer la commande</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
