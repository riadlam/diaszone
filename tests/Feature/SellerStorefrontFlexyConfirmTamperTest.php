<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorefrontFlexyConfirmTamperTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_fails_when_order_values_are_tampered()
    {
        $seller = Seller::factory()->create([
            'username' => 'tamper-seller',
            'website_enabled' => true,
            'flexy_enabled' => true,
            'wallet_balance' => 5000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Flexy Pack',
            'code' => 'FPTAMP',
            'diamonds' => 50,
            'bonus_diamonds' => 5,
            'price' => 5.00,
            'price_dzd' => 600.00,
            'base_price_dzd' => 500.00,
            'price_usd' => 2.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 700.00,
            'custom_price_usd' => 3.50,
            'flexy_price' => 666.00,
            'is_active' => true,
        ]);

        // Create an order but tamper with values (set seller_cost lower and final_price different)
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending_flexy_verification',
            // Tampered values
            'seller_cost' => 100.00,
            'seller_profit' => 50.00,
            'original_price' => 150.00,
            'final_price' => 150.00,
            'payment_method' => 'flexy',
            'is_direct_topup' => false,
        ]);

        // Ensure VipResellerService is not called because server should reject before placing VIP order
        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldNotReceive('placeOrder');
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        $this->actingAs($seller, 'seller');

        $resp = $this->patchJson(route('seller.orders.confirm', ['orderNumber' => $order->order_number]));
        $resp->assertStatus(400)->assertJson(['success' => false]);

        $order->refresh();
        $this->assertEquals('pending_flexy_verification', $order->status);
    }
}
