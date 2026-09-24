<?php

namespace App\Http\Controllers;

use App\Services\WalletException;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalRequestController extends Controller
{
    public function __construct(protected WithdrawalService $withdrawals)
    {
    }

    public function create(Request $request): View
    {
        $wallet = $request->user()->wallet;
        $history = $request->user()->withdrawalRequests()->latest()->paginate(10);

        return view('withdrawals.create', compact('wallet', 'history'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $this->withdrawals->requestWithdrawal($request->user(), (float) $validated['amount']);
        } catch (WalletException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('withdrawals.create')
            ->with('status', 'Demande de retrait envoyee, en attente de validation.');
    }
}
