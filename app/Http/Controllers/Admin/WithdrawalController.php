<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WalletException;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(protected WithdrawalService $withdrawals)
    {
    }

    public function index(): View
    {
        $withdrawals = WithdrawalRequest::with(['user', 'processor'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $validated = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255', 'required_if:is_partial,1'],
        ]);

        try {
            $this->withdrawals->approve(
                $withdrawal,
                (float) $validated['approved_amount'],
                $request->user(),
                $validated['reason'] ?? null
            );
        } catch (WalletException $e) {
            return back()->withErrors(['approved_amount' => $e->getMessage()]);
        }

        return back()->with('status', 'Demande de retrait approuvée.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->withdrawals->reject($withdrawal, $request->user(), $validated['reason']);

        return back()->with('status', 'Demande de retrait rejetée.');
    }
}
