<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::with('ambassador')->latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.coupons.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'               => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'ambassador_user_id' => ['required', 'exists:users,id'],
            'discount_type'      => ['required', 'in:percentage,fixed'],
            'value'              => ['required', 'numeric', 'min:0.01'],
            'buyer_share_percent'=> ['required', 'integer', 'min:0', 'max:100'],
            'usage_limit'        => ['nullable', 'integer', 'min:1'],
            'expires_at'         => ['nullable', 'date', 'after:today'],
        ]);

        Coupon::create($validated + [
            'usage_count' => 0,
            'is_active'   => true,
        ]);

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', 'Coupon créé avec succès.');
    }

    public function edit(Coupon $coupon): View
    {
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.coupons.edit', compact('coupon', 'users'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code'               => ['required', 'string', 'max:50', 'unique:coupons,code,' . $coupon->id],
            'ambassador_user_id' => ['required', 'exists:users,id'],
            'discount_type'      => ['required', 'in:percentage,fixed'],
            'value'              => ['required', 'numeric', 'min:0.01'],
            'buyer_share_percent'=> ['required', 'integer', 'min:0', 'max:100'],
            'usage_limit'        => ['nullable', 'integer', 'min:1'],
            'expires_at'         => ['nullable', 'date'],
        ]);

        $coupon->update($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', 'Coupon mis à jour avec succès.');
    }

    /**
     * Supprime un coupon uniquement s'il n'a jamais été utilisé dans une
     * commande payée (pour préserver l'intégrité historique des commissions).
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $usedInPaidOrder = $coupon->orders()->where('status', 'paid')->exists();

        if ($usedInPaidOrder) {
            return back()->with(
                'error',
                'Ce coupon ne peut pas être supprimé car il est associé à des commandes payées.'
            );
        }

        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', 'Coupon supprimé.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('status', $coupon->is_active
            ? 'Coupon activé.'
            : 'Coupon désactivé.');
    }
}
