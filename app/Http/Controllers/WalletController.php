<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function show(Request $request): View
    {
        $wallet = $request->user()->wallet;
        $transactions = $wallet->transactions()->paginate(15);

        return view('wallet.show', compact('wallet', 'transactions'));
    }
}
