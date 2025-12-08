<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\WalletRechargeAsk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerTopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_create_topup_request_and_wallet_remains_unchanged()
    {
        $seller = Seller::factory()->create([
            'wallet_balance' => 10000,
        ]);

        $this->actingAs($seller, 'seller');

        // Fake the public storage disk and include a fake receipt file
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('receipt.png', 100, 'image/png');

        $response = $this->post(route('seller.topup.request'), [
            'amount' => 5000,
            'seller_note' => 'Please top-up',
            'receipt' => $file,
            'payment_type' => 'crypto',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wallet_recharge_asks', [
            'seller_id' => $seller->id,
            'amount' => 5000,
            'status' => 'pending',
        ]);

        // currency must reflect payment_type: crypto -> USD
        $ask = WalletRechargeAsk::where('seller_id', $seller->id)->first();
        $this->assertEquals('USD', $ask->currency);

        // Ensure a receipt URL was saved and the file exists on the public disk
        $ask = WalletRechargeAsk::where('seller_id', $seller->id)->first();
        $this->assertNotNull($ask->receipt, 'Receipt URL should be saved in DB');

        // Stored URL should start with /storage/ (Storage::url)
        $this->assertStringContainsString('recharge_receipts', $ask->receipt);

        // Ensure the file exists on the fake public disk
        $storedPath = preg_replace('#^/storage/#', '', $ask->receipt);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($storedPath);

        // Wallet should remain unchanged until admin approves
        $seller->refresh();
        $this->assertEquals(10000, (int) $seller->wallet_balance);

        // No debit transaction should have been recorded as a result of the request
        $this->assertDatabaseMissing('seller_wallet_transactions', [
            'type' => 'debit',
            'amount' => 5000,
        ]);
    }

    public function test_seller_cannot_request_invalid_amount()
    {
        $seller = Seller::factory()->create([
            'wallet_balance' => 1000,
        ]);

        $this->actingAs($seller, 'seller');

        $response = $this->post(route('seller.topup.request'), [
            'amount' => 0,
            'payment_type' => 'ccp',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('wallet_recharge_asks', [
            'seller_id' => $seller->id,
            'amount' => 0,
        ]);

    }

    public function test_baridimob_requires_phone()
    {
        $seller = Seller::factory()->create([
            'wallet_balance' => 500,
        ]);

        $this->actingAs($seller, 'seller');

        $response = $this->post(route('seller.topup.request'), [
            'amount' => 200,
            'payment_type' => 'baridimob',
        ]);

        $response->assertSessionHasErrors('phone');
    }
}
