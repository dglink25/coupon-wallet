<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\WalletException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orders)
    {
    }

    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with('coupon')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('orders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $this->orders->createOrder(
                $request->user(),
                (float) $validated['amount'],
                $validated['coupon_code'] ?: null
            );
        } catch (WalletException $e) {
            return back()->withErrors(['coupon_code' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('orders.index')
            ->with('status', 'Commande simulee creee (en attente de paiement).');
    }

    /**
     * Marque la commande comme payee : c'est ce declencheur qui, en
     * presence d'un coupon, credite la commission de l'ambassadeur.
     */
    public function pay(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->orders->markAsPaid($order);

        return redirect()
            ->route('orders.index')
            ->with('status', 'Commande marquee comme payee.');
    }
}
