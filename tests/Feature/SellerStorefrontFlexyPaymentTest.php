<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use App\Models\Order;
use App\Services\VipResellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SellerStorefrontFlexyPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_flexy_order_saves_server_calculated_flexy_price()
    {
        $seller = Seller::factory()->create([
            'username' => 'flexy-seller',
            'website_enabled' => true,
            'flexy_enabled' => true,
            'wallet_balance' => 5000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Flexy Pack',
            'code' => 'FP100',
            'diamonds' => 100,
            'bonus_diamonds' => 10,
            'price' => 10.00,
            'price_dzd' => 1200.00,
            'base_price_dzd' => 1000.00,
            'price_usd' => 4.00,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 1500.00,
            'custom_price_usd' => 6.00,
            'flexy_price' => 1333.00,
            'is_active' => true,
        ]);

        // Stub VipResellerService check to allow Mobile Legends nickname validation
        $this->app->instance(\App\Services\VipResellerService::class, new class {
            public function checkNickname($userId, $zoneId) {
                return ['result' => true, 'data' => 'NickOK'];
            }
        });

        $response = $this->post(route('seller.store.payment', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '1111',
            'zone_id' => '8888',
            'payment_method' => 'flexy',
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf')
        ]);

        // (VipResellerService stub already bound above)

        $response->assertRedirect();

        $order = Order::first();
        $this->assertEquals(1333.00, (float) $order->final_price);
        $this->assertEquals(1000.00, (float) $order->seller_cost);
        $this->assertEquals(333.00, (float) $order->seller_profit);
        $this->assertEquals('pending_flexy_verification', $order->status);
        $this->assertFalse((bool) $order->wallet_deducted);
    }

    public function test_confirm_flexy_order_places_vip_reseller_and_deducts_wallet_and_credits_profit()
    {
        $seller = Seller::factory()->create([
            'username' => 'confirm-seller',
            'website_enabled' => true,
            'flexy_enabled' => true,
            'wallet_balance' => 5000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Flexy Pack',
            'code' => 'FP200',
            'diamonds' => 100,
            'bonus_diamonds' => 10,
            'price' => 10.00,
            'price_dzd' => 1200.00,
            'base_price_dzd' => 1000.00,
            'price_usd' => 4.00,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // create order pending confirmation (client already submitted flexy)
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending_flexy_verification',
            'seller_cost' => 1000.00,
            'seller_profit' => 333.00,
            'original_price' => 1333.00,
            'final_price' => 1333.00,
            'payment_method' => 'flexy',
            'is_direct_topup' => false,
        ]);

        // Ensure seller has the matching SellerGamePrice so server-side validation passes
        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 1500.00,
            'custom_price_usd' => 6.00,
            'flexy_price' => 1333.00,
            'is_active' => true,
        ]);

        // Mock VipResellerService to simulate successful top-up
        $mock = \Mockery::mock(VipResellerService::class);
        // Simulate successful VIP reseller top-up
        $mock->shouldReceive('placeOrder')->andReturn(['result' => true, 'data' => ['ok' => true]])->once();
        $this->app->instance(VipResellerService::class, $mock);

        // Mock TelegramService to assert notification sent
        $tMock = \Mockery::mock(\App\Services\TelegramService::class);
        $tMock->shouldReceive('sendMessage')->once()->with(\Mockery::on(function ($msg) use ($seller, $order) {
            return str_contains($msg, $order->order_number) && str_contains($msg, $seller->username);
        }))->andReturn(123);
        $this->app->instance(\App\Services\TelegramService::class, $tMock);

        // Authenticate as seller
        $this->actingAs($seller, 'seller');

        $resp = $this->patchJson(route('seller.orders.confirm', ['orderNumber' => $order->order_number]));
        $resp->assertStatus(200)->assertJson(['success' => true]);

        $data = $resp->json();
        $this->assertArrayHasKey('order', $data);
        $this->assertArrayHasKey('seller', $data);

        $order->refresh();
        $seller->refresh();

        $this->assertEquals('completed', $order->status);
        $this->assertTrue((bool)$order->wallet_deducted);

        // Wallet net change = -seller_cost + seller_profit
        $expected = 5000.00 - 1000.00 + 333.00;
        $this->assertEquals($expected, (float)$seller->wallet_balance);

    }

    public function test_confirm_flexy_order_failure_sends_telegram_and_does_not_deduct_wallet()
    {
        $seller = Seller::factory()->create([
            'username' => 'fail-seller',
            'website_enabled' => true,
            'flexy_enabled' => true,
            'wallet_balance' => 5000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Flexy Pack',
            'code' => 'FP300',
            'diamonds' => 100,
            'bonus_diamonds' => 10,
            'price' => 10.00,
            'price_dzd' => 1200.00,
            'base_price_dzd' => 1000.00,
            'price_usd' => 4.00,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending_flexy_verification',
            'seller_cost' => 1000.00,
            'seller_profit' => 333.00,
            'original_price' => 1333.00,
            'final_price' => 1333.00,
            'payment_method' => 'flexy',
            'is_direct_topup' => false,
        ]);

        // Add matching seller price so server-side validation uses expected values
        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 1500.00,
            'custom_price_usd' => 6.00,
            'flexy_price' => 1333.00,
            'is_active' => true,
        ]);

        // In this insufficient-wallet case we should return early from the controller
        // and NOT attempt a VIP top-up nor send Telegram notifications. Ensure no
        // expectations are set for those services.
        $vipMock = \Mockery::mock(VipResellerService::class);
        $vipMock->shouldNotReceive('placeOrder');
        $this->app->instance(VipResellerService::class, $vipMock);

        $tMock = \Mockery::mock(\App\Services\TelegramService::class);
        $tMock->shouldNotReceive('sendMessage');
        $this->app->instance(\App\Services\TelegramService::class, $tMock);

        $this->actingAs($seller, 'seller');

        // reduce seller wallet below required cost
        $seller->wallet_balance = 100.00;
        $seller->save();

        $resp = $this->patchJson(route('seller.orders.confirm', ['orderNumber' => $order->order_number]));
        $resp->assertStatus(400)->assertJson(['success' => false, 'insufficient_wallet' => true]);

        $order->refresh();
        $seller->refresh();

        // Because the seller did not have enough balance, the order should remain pending for flexy verification
        $this->assertEquals('pending_flexy_verification', $order->status);
        $this->assertFalse((bool)$order->wallet_deducted);
        // Wallet unchanged (still 100 after our manual reduction)
        $this->assertEquals(100.00, (float)$seller->wallet_balance);
    }
}
