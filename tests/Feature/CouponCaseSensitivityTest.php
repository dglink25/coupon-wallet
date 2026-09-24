<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponCaseSensitivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_lookup_is_case_sensitive(): void
    {
        $ambassador = User::factory()->create();

        Coupon::create([
            'code' => 'Promo25',
            'ambassador_user_id' => $ambassador->id,
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'value' => 25,
            'buyer_share_percent' => 50,
            'is_active' => true,
        ]);

        $this->assertNotNull(Coupon::findByCode('Promo25'));
        $this->assertNull(Coupon::findByCode('promo25'));
        $this->assertNull(Coupon::findByCode('PROMO25'));
    }
}
