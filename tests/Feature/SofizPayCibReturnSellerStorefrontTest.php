<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SofizPayCibTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SofizPayCibReturnSellerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_sofizpay_return_completes_seller_storefront_order_with_vip_provider(): void
    {
        config([
            'services.chargily_pay_v2.secret' => 'unused',
            'services.digiflazz.username' => null,
            'services.digiflazz.sign' => null,
            'services.sofizpay.enabled' => true,
            'services.sofizpay.sandbox' => false,
            'services.sofizpay.base_url' => 'https://sofizpay.com',
            'services.sofizpay.merchant_account' => 'GA_SELLER_TEST',
        ]);
        putenv('DIGIFLAZZ_USERNAME');
        putenv('DIGIFLAZZ_SIGN');

        Http::fake([
            'https://sofizpay.com/cib-transaction-check*' => Http::response($this->paidCheckPayload('600.00', 'GA_SELLER_TEST'), 200),
        ]);

        $seller = Seller::factory()->create([
            'username' => 'webhookseller',
            'website_enabled' => true,
            'wallet_balance' => 2000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Webhook Pack',
            'code' => 'WP100',
            'diamonds' => 50,
            'bonus_diamonds' => 0,
            'price' => 5.00,
            'price_dzd' => 600.00,
            'base_price_dzd' => 500.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending_bmccp',
            'user_id_ml' => 'player123',
            'zone_id_ml' => '1',
            'wallet_deducted' => false,
            'seller_cost' => 500.00,
            'seller_profit' => 100.00,
            'original_price' => 600.00,
            'final_price' => 600.00,
            'payment_method' => 'baridimob',
            'chargily_status_id' => null,
        ]);

        $spf = SofizPayCibTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'txn-seller-1',
            'cib_order_number' => '2848000001',
            'amount_expected' => 600.00,
            'status' => 'pending',
            'create_response' => [],
        ]);
        $order->update(['sofizpay_cib_transaction_id' => $spf->id]);

        $mockVip = \Mockery::mock(\App\Services\VipResellerService::class);
        $mockVip->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'nick']);
        $mockVip->shouldReceive('placeOrder')->andReturn([
            'result' => true,
            'data' => ['trxid' => 't1', 'status' => 'success'],
            'message' => 'ok',
        ]);
        $mockVip->shouldReceive('getProfile')->andReturn(['result' => true, 'data' => ['balance' => 1000]]);
        $this->app->instance(\App\Services\VipResellerService::class, $mockVip);

        $eid = Crypt::encryptString((string) $order->id);
        $response = $this->get(route('payment.sofizpay.cib.return', ['eid' => $eid]));

        $response->assertRedirect(route('seller.payment.success', ['encrypted_order_id' => $eid]));

        $order->refresh();
        $seller->refresh();

        $this->assertEquals('completed', $order->status);
        $this->assertEquals(1600.00, (float) $seller->wallet_balance);

        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id, 'trxid' => 't1', 'status' => 'success']);
    }

    /**
     * @return array<string, mixed>
     */
    private function paidCheckPayload(string $amount, string $destinationAccount): array
    {
        return [
            'respCode' => '00',
            'errorCode' => '0',
            'orderStatus' => '2',
            'Amount' => $amount,
            'destination_account' => $destinationAccount,
        ];
    }
}
