<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Wallet;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_is_not_credited_before_payment(): void
    {
        [$ambassador, $buyer, $coupon] = $this->makeCoupon();

        $service = app(OrderService::class);
        $order = $service->createOrder($buyer, 1000, $coupon->code);

        $this->assertSame(0.0, (float) $ambassador->wallet->fresh()->balance);
        $this->assertSame('pending', $order->status);
    }

    public function test_commission_is_credited_exactly_once_when_replayed(): void
    {
        [$ambassador, $buyer, $coupon] = $this->makeCoupon();

        $service = app(OrderService::class);
        $order = $service->createOrder($buyer, 1000, $coupon->code);

        // On "rejoue" volontairement l'action plusieurs fois, comme le
        // ferait un double-clic utilisateur ou un retry reseau.
        $service->markAsPaid($order->fresh());
        $service->markAsPaid($order->fresh());
        $service->markAsPaid($order->fresh());

        $expectedCommission = $coupon->computeSplit(1000)['commission'];

        $this->assertEquals($expectedCommission, (float) $ambassador->wallet->fresh()->balance);
        $this->assertSame(
            1,
            \App\Models\WalletTransaction::where('reference_type', 'order_commission')
                ->where('reference_id', $order->id)
                ->count()
        );
    }

    protected function makeCoupon(): array
    {
        $ambassador = User::factory()->create();
        Wallet::create(['user_id' => $ambassador->id, 'balance' => 0]);

        $buyer = User::factory()->create();
        Wallet::create(['user_id' => $buyer->id, 'balance' => 0]);

        $coupon = Coupon::create([
            'code' => 'TEST10',
            'ambassador_user_id' => $ambassador->id,
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10,
            'buyer_share_percent' => 50,
            'usage_limit' => null,
            'usage_count' => 0,
            'is_active' => true,
        ]);

        return [$ambassador, $buyer, $coupon];
    }
}
