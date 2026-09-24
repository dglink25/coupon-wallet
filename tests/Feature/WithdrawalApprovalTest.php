<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Services\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_is_not_debited_on_simple_request(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 500]);

        $service = app(WithdrawalService::class);
        $service->requestWithdrawal($user, 200);

        $this->assertEquals(500, (float) $wallet->fresh()->balance);
    }

    public function test_approving_twice_only_debits_once(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 500]);

        $service = app(WithdrawalService::class);
        $withdrawal = $service->requestWithdrawal($user, 200);

        $service->approve($withdrawal->fresh(), 200, $admin);
        // Deuxieme appel : la demande n'est plus "pending", donc ignoree.
        $service->approve($withdrawal->fresh(), 200, $admin);

        $this->assertEquals(300, (float) $wallet->fresh()->balance);
    }
}
