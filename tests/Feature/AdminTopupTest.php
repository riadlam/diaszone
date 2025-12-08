<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\WalletRechargeAsk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_topup_and_wallet_is_credited()
    {
        $seller = Seller::factory()->create([
            'wallet_balance' => 1000,
        ]);

        $topup = WalletRechargeAsk::create([
            'seller_id' => $seller->id,
            'amount' => 5000,
            'currency' => 'DZD',
            'payment_type' => 'crypto',
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $this->actingAs($admin);

        $response = $this->patch(route('admin.topups.approve', ['topup' => $topup->id]), ['admin_note' => 'Approved']);

        $topup->refresh();
        $this->assertEquals('approved', $topup->status);

        $seller->refresh();
        $this->assertEquals(6000, (int) $seller->wallet_balance);

        $this->assertDatabaseHas('seller_wallet_transactions', [
            'type' => 'credit',
            'amount' => 5000,
        ]);
    }

    public function test_admin_can_reject_topup_and_wallet_is_unchanged()
    {
        $seller = Seller::factory()->create([
            'wallet_balance' => 1000,
        ]);

        $topup = WalletRechargeAsk::create([
            'seller_id' => $seller->id,
            'amount' => 3000,
            'currency' => 'DZD',
            'payment_type' => 'ccp',
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $this->actingAs($admin);

        $response = $this->patch(route('admin.topups.reject', ['topup' => $topup->id]), ['admin_note' => 'Not possible']);

        $topup->refresh();
        $this->assertEquals('rejected', $topup->status);

        $seller->refresh();
        $this->assertEquals(1000, (int) $seller->wallet_balance);
    }
}
