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
        // Seuls les utilisateurs non-admin peuvent etre designes ambassadeurs
        // (un compte admin gere la plateforme, il ne partage pas de coupon).
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.coupons.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'ambassador_user_id' => ['required', 'exists:users,id'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'buyer_share_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        Coupon::create($validated + [
            'usage_count' => 0,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', 'Coupon cree avec succes.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('status', $coupon->is_active
            ? 'Coupon active.'
            : 'Coupon desactive.');
    }
}
