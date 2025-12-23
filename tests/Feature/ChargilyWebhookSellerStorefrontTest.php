<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Seller;
use App\Models\ChargilyStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargilyWebhookSellerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_finds_order_created_by_seller_storefront()
    {
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

        // Simulate order creation from storefront
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending',
            'user_id_ml' => 'player123',
            'zone_id_ml' => '1',
            'wallet_deducted' => false,
            'seller_cost' => 500.00,
            'seller_profit' => 100.00,
            'original_price' => 600.00,
            'final_price' => 600.00,
            'payment_method' => 'baridimob',
        ]);

        // Create a chargily_status with checkout_id and link to order
        $status = ChargilyStatus::create([
            'order_id' => $order->id,
            'checkout_id' => 'ch_test_456',
            'event_type' => 'checkout.created',
            'status' => 'pending',
            'amount' => 600,
            'currency' => 'DZD',
        ]);

        $order->update(['chargily_status_id' => $status->id]);

        // Now call the webhook endpoint with a payload similar to Chargily
        $payload = [
            'id' => 'evt_1',
            'entity' => 'event',
            'type' => 'checkout.paid',
            'data' => [
                'id' => 'ch_test_456',
                'status' => 'paid',
                'amount' => 600,
            ],
        ];

        // Configure API secret and ensure Digiflazz is not configured for this legacy test
        config(['services.chargily_pay_v2.secret' => 'testsecret', 'services.digiflazz.username' => null, 'services.digiflazz.sign' => null]);
        putenv('DIGIFLAZZ_USERNAME=');
        putenv('DIGIFLAZZ_SIGN=');

        $mockVip = \Mockery::mock(\App\Services\VipResellerService::class);
        $mockVip->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'nick']);
        $mockVip->shouldReceive('placeOrder')->andReturn(['result' => true, 'data' => ['trxid' => 't1', 'status' => 'success'], 'message' => 'ok']);
        $mockVip->shouldReceive('getProfile')->andReturn(['result' => true, 'data' => ['balance' => 1000]]);
        $this->app->instance(\App\Services\VipResellerService::class, $mockVip);

        // Compute signature and send webhook request
        $raw = json_encode($payload);
        $signature = hash_hmac('sha256', $raw, 'testsecret');

        $response = $this->withHeaders(['signature' => $signature])->postJson(route('baridimob.webhook'), $payload);

        $response->assertStatus(200);

        // Reload order and seller; ensure status moved to completed and profit was credited
        $order->refresh();
        $seller->refresh();

        $this->assertEquals('completed', $order->status);

        // Wallet should have been deducted (seller_cost) then credited with profit
        // initial 2000 - seller_cost(500) + seller_profit(100) = 1600
        $this->assertEquals(1600.00, (float) $seller->wallet_balance);

        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id, 'trxid' => 't1', 'status' => 'success']);
    }
}
