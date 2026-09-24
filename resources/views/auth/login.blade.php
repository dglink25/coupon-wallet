@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold">General <span style="color:#b3134a">Invasion</span></h2>
            <p class="text-secondary">Mini-systeme de coupon promotionnel &amp; portefeuille</p>
        </div>
        <div class="card p-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Connexion</h5>

                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Adresse e-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-brand w-100">Se connecter</button>
                </form>
            </div>
        </div>
        <p class="text-center text-secondary small mt-3">
            Comptes de demonstration (mot de passe : <code>password</code>) :<br>
            admin@generalinvasion.com · ambassadeur@generalinvasion.com · acheteur@generalinvasion.com
        </p>
    </div>
</div>
@endsection
